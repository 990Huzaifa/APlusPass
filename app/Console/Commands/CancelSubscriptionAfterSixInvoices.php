<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Invoice;
use Stripe\Subscription;
use Carbon\Carbon;

class CancelSubscriptionAfterSixInvoices extends Command
{

    protected $signature = 'app:cancel-subscription-after-six-invoices';
    protected $description = 'Command description';

     public function __construct()
    {
        parent::__construct();

        Stripe::setApiKey('sk_test_51MuE4RJIWkcGZUIa0JLTtCVh5g2ZqyqDuXDbxmT4kNqsR1oI2VEOcQXcA6Iojo1yqV7mo2GKMjkTlW76Sk3gVZW400nUHWXlJH');
    }
    public function handle()
    {
        $subscriptions = Payment::where('payment_type', 'subscription')
        ->where('status', 1)
        ->get();

        foreach ($subscriptions as $subscription) {
            $stripeSubscriptionId = $subscription->transaction_id;
            $invoices = Invoice::all([
                'subscription' => $stripeSubscriptionId,
                'status' => 'paid',
                'limit' => 100,
            ]);

            $paidInvoices = $invoices->data;
            $paidInvoiceCount = count($invoices->data);
            // Retrieve the subscription from Stripe
            


             if ($paidInvoiceCount >= 6) {

                $sixthInvoice = $paidInvoices[5];  // 6th invoice (index 5 because it's zero-indexed)

                $sixthInvoiceDate = Carbon::createFromTimestamp($sixthInvoice->created);
                $currentDate = Carbon::now();

                // Cancel the subscription on Stripe
                if ($currentDate->diffInMonths($sixthInvoiceDate) >= 6) {
                    
                    $stripeSubscription = Subscription::retrieve($stripeSubscriptionId);
                    $stripeSubscription->cancel();
                    
                    
                    $subscription->status = 0;
                    $subscription->save();
                }
            }
        }


        return 0;
    }
}
