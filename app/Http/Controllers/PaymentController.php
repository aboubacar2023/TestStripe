<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        if (empty($request)) {
            return view('welcome');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        // On récupère le produit demandé
        $produit = Produit::findOrFail($request->input('product_id'));
        $quantite = (int) $request->input('quantite', 1);
        $user = User::first();

        // Vérification/Création du client Stripe
        if (!$user->stripe_customer_id) {
            $stripeCustomer = Customer::create([
                'name' => $user->name,
                'email' => $user->email,
            ]);

            $user->stripe_customer_id = $stripeCustomer->id;
            $user->save();
        }

        $montant = $produit->price * $quantite * 100;

        // Création de la session Checkout
        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Achat ' . strtoupper($produit->nom_produit),
                            'description' => 'Achat de ' . $quantite . ' ' . $produit->nom_produit,
                        ],
                        'unit_amount' => $montant,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'customer' => $user->stripe_customer_id,
            'payment_intent_data' => [
                'description' => 'Achat de ' . $quantite . ' ' . $produit->nom_produit,
            ],
            'success_url' => route('checkout.success'),
            'cancel_url' => route('welcome'),
        ]);

        // Ici, on peut rediriger directement vers Stripe
        return redirect($checkoutSession->url);
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
            $paymentIntent = $event->data->object;
            $customerId = $paymentIntent->customer;
            $paymentIntentId = $paymentIntent->id;

            $user = User::where('stripe_customer_id', $customerId)->first();

            if ($user) {
                Abonnement::firstOrCreate([
                    'user_id' => $user->id,
                    'stripe_payment_intent_id' => $paymentIntentId,
                    'stripe_customer_id' => $customerId,
                    'status' => 'active',
                    'plan' => 'premium',
                    'start_date' => now(),
                    'end_date' => now()->addMonth(),
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
