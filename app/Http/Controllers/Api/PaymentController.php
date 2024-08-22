<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Stripe\StripeClient;
use Stripe\Stripe;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Session;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\Admin\InvoiceMail;
use App\Mail\User\SuccessMail;
use App\Services\GoogleSheetService;

class PaymentController extends Controller
{
    protected $stripeClient;
    protected $googleSheetsService;

    public function __construct( GoogleSheetService $googleSheetsService)
    {
        $this->stripeClient = new StripeClient('sk_test_51MuE4RJIWkcGZUIa0JLTtCVh5g2ZqyqDuXDbxmT4kNqsR1oI2VEOcQXcA6Iojo1yqV7mo2GKMjkTlW76Sk3gVZW400nUHWXlJH');
        $this->googleSheetsService = $googleSheetsService;
    }

    public function pay(Request $request)
    {
        
        try {
            if($request->payment_type == 'one_time'){
                $plan = Plan::where('name',$request->courses)->first();
                $discount= $request->discount ?? 0; //1080
                
                
                $price = $plan->amount /100 *(100 - $discount);
                $amount = $price * 100; // amount in cents
                
                
                
                $p_name = 'Course'.' '.$plan->name;
                $session = $this->stripeClient->checkout->sessions->create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => $p_name,
                            ],
                            'unit_amount' => $amount,
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => url('https://apluspass.zetdigi.com/apluspass/public/api/op-success'),
                    'cancel_url' => route('cancel'),
                    'metadata' => [
                        'payment_type' => 'one_time',
                        'description' => $plan->description,
                        'name' => $p_name,
                        'amount' => $plan->amount,
                    ]
                ]);
                $this->setSession($session->id);
                
                
                
            }
            elseif($request->payment_type == 'subscription'){
                $plan = Plan::where('name',$request->courses)->first();
                $session= $this->stripeClient->checkout->sessions->create([
                    'payment_method_types' => ['card'],
                    'line_items' => [
                        [
                        'price' => $plan->price_id,
                        'quantity' => 1,
                        ],
                    ],
                    'mode' => 'subscription',
                    'success_url' => url('https://apluspass.zetdigi.com/apluspass/public/api/op-success'),
                    'cancel_url' => route('cancel'),
                    'metadata' => [
                        'payment_type' => 'subscription',
                        'description' => $plan->description,
                        'name' => $plan->name,
                        'amount' => $plan->amount,
                        'session_id'=>'{CHECKOUT_SESSION_ID}',
                    ]
                ]);
                
                $this->setSession($session->id);
            }
            
            
            return $session->url;
    
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
        

    }

    // stripe payment intent function
    
    
    public function createPaymentIntent(Request $request):JsonResponse
    {

        try{
            $validator = Validator::make(
                $request->all(),[
            'amount' => 'required|numeric|min:1'
            ],
            [
                'amount.required' => 'Amount is required.',
                
            ]);
            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);
            
            // Create a payment intent and retrieve the client secret
            $paymentIntent = $this->stripeClient->paymentIntents->create([
                'amount' => $request->input('amount') * 100, // Convert amount to cents
                'currency' => 'usd',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);
    
            // Return the client secret in the response
            return response()->json($paymentIntent->client_secret);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
        
    }
    

    public function createSubscriptionStripe(Request $request)
    {
        
        try {
            
            $validator = Validator::make(
                $request->all(),
                [
                'user_email' => 'required|email',
                'user_name'=>'required',
                'courses'=>'required',
                ],
                [
                    'user_email.required' => 'Email is Required',
                    'user_name.required'=>'Name is Required',
                    'courses.required'=>'Plan ID is Required',
                    
                ]);
            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);
            
            $customerEmail = $request->input('user_email');
            $customerName = $request->input('user_name');
            $plan = Plan::select('price_id')->where('name',$request->courses)->first();
            $priceId = $plan->price_id;
            // Create or retrieve the customer
            $customer = $this->stripeClient->customers->create([
                'email' => $customerEmail,
                'name' => $customerName
            ]);

            // Create the subscription
            $subscription = $this->stripeClient->subscriptions->create([
                'customer' => $customer->id,
                'items' => [['price' => $priceId]],
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            // Send the subscription ID and client secret to the client
            return response()->json([
                'client_secret'=>$subscription->latest_invoice->payment_intent->client_secret,
                'subscription_id'=>$subscription->id
                ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    
    // public function createSubscriptionStripe(Request $request)
    // {
        
    //     try {
            
    //         $validator = Validator::make(
    //             $request->all(),
    //             [
    //                 'user_email' => 'required|email',
    //                 'user_name'=>'required',
    //                 'courses'=>'required',
    //             ],
    //             [
    //                 'user_email.required' => 'Email is Required',
    //                 'user_name.required'=>'Name is Required',
    //                 'courses.required'=>'Plan ID is Required',
                    
    //             ]);
    //         if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);
            
    //         $customerEmail = $request->input('user_email');
    //         $customerName = $request->input('user_name');
    //         $plan = Plan::select('price_id')->where('name',$request->courses)->first();
    //         $priceId = $plan->price_id;
    //         // Create or retrieve the customer
    //         $customer = $this->stripeClient->customers->create([
    //             'email' => $customerEmail,
    //             'name' => $customerName
    //         ]);

    //         $subscription = $this->stripeClient->subscriptions->create([
    //             'customer' => $customer->id,
    //             'items' => [['price' => $priceId]],
    //             'payment_behavior' => 'default_incomplete', // Ensure an invoice and payment intent are created immediately
    //             'expand' => ['latest_invoice.payment_intent'],
    //         ]);
    
    //         // Retrieve the PaymentIntent from the created subscription
    //         $paymentIntent = $subscription->latest_invoice->payment_intent;
    
    //         if (!$paymentIntent) {
    //             throw new Exception('Failed to create payment intent', 500);
    //         }

    //         // Send the subscription ID and client secret to the client
    //         return response()->json($paymentIntent);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
    //     }
    // }


    public function success(Request $request)
    {  
        try {
            $validator = Validator::make(
                $request->all(),
                [
                'user_email' => 'required|email',
                'user_name'=>'required',
                'transaction_id'=>'required',
                'amount' => 'required|numeric|min:1',
                'description'=>'required',
                'payment_type'=>'required'
                ],
                [
                    'user_email.required' => 'Email is Required',
                    'user_name.required'=>'Name is Required',
                    'transaction_id.required'=>'Transaction ID is Required',
                    'amount.required' => 'Amount is required.',
                    'description.required'=>'Description is required',
                    'payment_type.required'=>'Payment type is required'
                    
                ]);
            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);
            
            
            // Extract the payer details from the session
            $payerName = $request->user_name;
            $payerEmail = $request->user_email;
            $amount = '';
            if($request->payment_type == 'one_time'){
                $amount = $request->amount /100;
            }else{
                $amount = $request->amount;
            }
            
            // Retrieve additional data from the session metadata
            $paymentType = $request->payment_type;
            $description = $request->description;
            $productName = $request->name;
            
            // Store the payment details in the database
            $payment = new Payment();
            $payment->transaction_id = $request->transaction_id;
            $payment->amount = $amount;
            $payment->payer_name = $payerName;
            $payment->payer_email = $payerEmail;
            $payment->payment_type = $paymentType;
            $payment->description = $description;
            $payment->save();
            
            // Append the data to Google Sheets
            $data = [
                [
                    $request->transaction_id,
                    $amount,
                    $description,
                    $payerName,
                    $payerEmail,
                    $paymentType,
                    now()->toDateTimeString()
                ]
            ];
            
            Mail::to('surajkumar00244vk@gmail.com')->send(new InvoiceMail([
                'payerName' => $payerName,
                'email' => $payerEmail,
                'description' => $description,
                'amount' => $amount,
                'paymentType'=>$paymentType
                ]));

            Mail::to('surajkumar00244vk@gmail.com')->send(new SuccessMail([
                'payerName' => $payerName,
                'email' => $payerEmail,
                'description' => $description,
                'amount' => $amount,
                'paymentType'=>$paymentType
            ]));
            
            $range = 'transaction!A2:D2';
            $this->googleSheetsService->appendDataToSheet($data, $range);
            return response()->json('success', 201);
    
        } catch (Exception $e) {
           return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cancel(Request $request)
    {
        Log::info('Payment cancelled', $request->all());
        return "Payment is cancelled.";
    }
}
