<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Webhook;
use Stripe\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        // $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        $endpoint_secret = 'we_1PqskIJIWkcGZUIa3i3W9NIv';

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        if ($event->type == 'invoice.payment_succeeded') {
            $invoice = $event->data->object;
            $this->handleInvoicePaymentSucceeded($invoice);
        }

        return response()->json(['status' => 'success'], 200);
    }

    protected function handleInvoicePaymentSucceeded($invoice)
    {
        $subscriptionId = $invoice->subscription;
        $invoices = Invoice::all([
            'subscription' => $subscriptionId,
            'status' => 'paid',
            'limit' => 100,
        ]);

        $paidInvoices = $invoices->data;
        $paidInvoiceCount = count($paidInvoices);

        if ($paidInvoiceCount >= 6) {
            $sixthInvoice = $paidInvoices[5];  // 6th invoice (index 5 because it's zero-indexed)
            $sixthInvoiceDate = Carbon::createFromTimestamp($sixthInvoice->created);
            $currentDate = Carbon::now();

            // Check if the 6th invoice is at least 6 months old
            if ($currentDate->diffInMonths($sixthInvoiceDate) >= 6) {
                $stripeSubscription = Subscription::retrieve($subscriptionId);
                $stripeSubscription->cancel();

                // Here you can also update your database if needed
                Log::info("Subscription {$subscriptionId} canceled after 6 invoices.");
            } else {
                Log::info("The 6th invoice is not old enough to cancel the subscription.");
            }
        }
    }
}
