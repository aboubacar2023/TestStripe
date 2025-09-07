<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function index()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $user = User::first();

        if (!$user->stripe_customer_id) {
            $stripeCustomer = Customer::create([
                'name'  => $user->name,
                'email' => $user->email,
            ]);

            $user->stripe_customer_id = $stripeCustomer->id;
            $user->save();
        }

        $paymentIntent = PaymentIntent::create([
            'amount' => 100 * 100,
            'currency' => 'usd',
            'automatic_payment_methods' => ['enabled' => true],
            'customer' => $user->stripe_customer_id,
            'description' => 'Activation Premium',
        ]);

        return view('test', [
            'clientSecret' => $paymentIntent->client_secret,
            'stripeKey'    => config('services.stripe.key'),
        ]);
    }

    // Webhook Stripe
    public function webhook(Request $request)
    {
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        // Paiement réussi
        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent   = $event->data->object;
            $customerId      = $paymentIntent->customer;
            $paymentIntentId = $paymentIntent->id;

            $user = User::where('stripe_customer_id', $customerId)->first();

            if ($user) {
                Abonnement::firstOrCreate([
                    'user_id'                => $user->id,
                    'stripe_payment_intent_id' => $paymentIntentId,
                    'stripe_customer_id'       => $customerId,
                    'status'                   => 'active',
                    'plan'                     => 'premium',
                    'start_date'               => now(),
                    'end_date'                 => now()->addMonth(),
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
