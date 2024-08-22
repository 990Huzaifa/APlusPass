<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Invoice;
use Stripe\Subscription;
use App\Models\Payments;

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
        $subscriptions = Payment::where('payment_type', 'subscription')->get();

        // foreach ($subscriptions as $subscription) {
        //     $stripeSubscriptionId = $subscription->stripe_subscription_id;

        //     $invoices = Invoice::all([
        //         'subscription' => $stripeSubscriptionId,
        //         'status' => 'paid',
        //         'limit' => 100,
        //     ]);
        //     $paidInvoiceCount = count($invoices->data);
        //     // Retrieve the subscription from Stripe
            


        //     if ($paidInvoiceCount >= 6) {
        //         // Cancel the subscription on Stripe
        //         $stripeSubscription = Subscription::retrieve($stripeSubscriptionId);
        //         $stripeSubscription->cancel();

        //         // Update local subscription status
        //         $subscription->status = 'canceled';
        //         $subscription->save();

        //         $this->info("Subscription {$stripeSubscriptionId} canceled after 6 invoices.");
        //     }
        // }

        $invoices = Invoice::all([
            'subscription' => 'sub_1PqZj5JIWkcGZUIaKlnkSmLk',
            'status' => 'paid',
            'limit' => 100,
        ]);

        return $invoices;
    }
}
