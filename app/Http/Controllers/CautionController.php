<?php

namespace App\Http\Controllers;

use App\Models\Caution;
use App\Models\Produit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Stripe;
use Stripe\Webhook;

class CautionController extends Controller
{
    public function location()
    {
        return view('caution.location');
    }

    // Création de la session Stripe
    public function startLocation()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $user = User::where('id', 3)->first();

        // Ensure Stripe customer
        if (!$user->stripe_customer_id) {
            $cus = Customer::create([
                'name' => $user->name,
                'email' => $user->email,
            ]);

            $user->stripe_customer_id = $cus->id;
            $user->save();
        }

        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => "Location d'une voiture",
                            'description' => "Locaton d'une voiture",
                        ],
                        'unit_amount' => 400 * 100,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'customer' => $user->stripe_customer_id,
            'payment_intent_data' => [
                'setup_future_usage' => 'off_session',
                'description' => "Locaton d'une voiture",
            ],
            'success_url' => route('success'),
            'cancel_url' => route('location'),
        ]);

        // Ici, on peut rediriger directement vers Stripe
        return redirect($checkoutSession->url);
    }

    public function index()
    {
        $cautions = Caution::with('user')->latest()->get();
        return view('caution.admin', compact('cautions'));
    }

    public function capture(Request $request, Caution $caution)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $amount = $request->amount * 100;

        $intent = PaymentIntent::retrieve($caution->payment_intent_id);
        $intent->capture(['amount_to_capture' => $amount]);

        $caution->status = 'capture';
        $caution->montant_paye = $request->amount;
        $caution->save();

        Log::info("Stripe capture response", $intent->toArray());

        return back()->with('success', 'Caution capturée !');
    }

    public function annule(Caution $caution)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $intent = PaymentIntent::retrieve($caution->payment_intent_id);
        $intent->cancel();

        $caution->status = 'libere';
        $caution->save();

        return back()->with('success', 'Caution libérée !');
    }

    // Webhook Stripe
    public function webhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response('', 400);
        }
        

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $customerId = $session->customer;
            $paymentIntentId = $session->payment_intent;

            try {
                $pi = PaymentIntent::retrieve($paymentIntentId);
                $paymentMethodId = $pi->payment_method;


                $cautionAmount = 1000 * 100;

                $cautionIntent = PaymentIntent::create([
                    'amount' => $cautionAmount,
                    'currency' => 'eur',
                    'customer' => $customerId,
                    'payment_method' => $paymentMethodId,
                    'off_session' => true,
                    'confirm' => true,
                    'capture_method' => 'manual',
                ]);

                $user = User::where('stripe_customer_id', $customerId)->first();

                Caution::create([
                    'user_id' => $user->id,
                    'payment_intent_id' => $cautionIntent->id,
                    'montant' => $cautionAmount/100,
                    'montant_paye' => 0,
                    'status' => 'bloque',
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur création caution: ' . $e->getMessage());
            }
        }

        return response()->json(['status' => 'success']);
    }
}
