<?php

namespace App\Http\Controllers;

use App\Models\Caution;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class CautionController extends Controller
{
    public function inscription()
    {
        $produits = Produit::all(); // adapte si tu as pagination
        return view('caution.inscription', compact('produits'));
    }

    // Création de la session Stripe (caution / pré-autorisation)
    public function startEssaie()
    {

        Stripe::setApiKey(config('services.stripe.secret'));

        $user = User::first();

        // Ensure Stripe customer
        if (!$user->stripe_customer_id) {
            $cus = Customer::create([
                'name' => $user->name,
                'email' => $user->email,
            ]);
            $user->stripe_customer_id = $cus->id;
            $user->save();
        }

        // Create Checkout Session with manual capture (caution)
        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => 100*100,
                    'product_data' => [
                        'name' => 'Essaie Gratuit',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer' => $user->stripe_customer_id,
            'payment_intent_data' => [
                'capture_method' => 'manual',
                'description' => 'Essai gratuit pour 100 €'
            ],
            'success_url' => route('essaie'),
            'cancel_url' => route('essaie.inscription'),
        ]);

        // Store a caution row. stripe_payment_intent_id may be null at this moment.
        Caution::create([
            'user_id' => $user->id,
            'stripe_session_id' => $checkoutSession->id,
            'stripe_payment_intent_id' => $checkoutSession->payment_intent,
            'montant' => 100,
            'status' => 'pending',
            'start_date' => now(),
            'end_date' => now()->addDays(5),
        ]);

        // redirect to Stripe Checkout
        return redirect($checkoutSession->url);
    }

    // Vue de suivi / page après retour (success/cancel) : montre l'état de la caution actuelle
    public function showEssaie()
    {
        $user = User::first();
        $caution = Caution::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->first();

        return view('caution.suivie-essaie', compact('caution'));
    }

    // L'utilisateur confirme l'essai -> on capture
    public function confirmerEssaie(Caution $caution)
    {

        Stripe::setApiKey(config('services.stripe.secret'));
        try {
            $pi = PaymentIntent::retrieve($caution->stripe_payment_intent_id);
            $pi->capture();
            $caution->update(['status' => 'captured']);
            return redirect()->route('checkout.success');
        } catch (\Exception $e) {
            Log::error('Capture error: '.$e->getMessage());
            return redirect()->view('erreur');
        }
    }

    // L'utilisateur annule -> on annule la caution
    public function cancelEssaie(Caution $caution)
    {

        Stripe::setApiKey(config('services.stripe.secret'));
        try {
            $pi = PaymentIntent::retrieve($caution->stripe_payment_intent_id);
            $pi->cancel();
            $caution->update(['status' => 'canceled']);
            return redirect()->route('checkout.cancel');
        } catch (\Exception $e) {
            Log::error('Cancel error: '.$e->getMessage());
            return redirect()->view('erreur');
        }
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
            Log::error('Webhook signature error: '.$e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }

        // Handle relevant events
        $type = $event->type;
        $obj = $event->data->object;

        if ($type === 'checkout.session.completed') {
            // Session created / completed; link PI -> our caution row
            $session = $obj;
            $caution = Caution::where('stripe_session_id', $session->id)->first();
            if ($caution) {
                $caution->update([
                    'stripe_payment_intent_id' => $session->payment_intent ?? $caution->stripe_payment_intent_id,
                    'status' => 'pending',
                ]);
            }
        }

        if ($type === 'payment_intent.succeeded' || $type === 'payment_intent.captured') {
            $pi = $obj;
            $caution = Caution::where('stripe_payment_intent_id', $pi->id)->first();
            if ($caution) { $caution->update(['status' => 'captured']); }
        }

        if ($type === 'payment_intent.canceled' || $type === 'payment_intent.payment_failed') {
            $pi = $obj;
            $caution = Caution::where('stripe_payment_intent_id', $pi->id)->first();
            if ($caution) { $caution->update(['status' => 'canceled']); }
        }

        return response()->json(['received' => true]);
    }

    // Back-office: lister toutes les cautions
    public function adminIndex()
    {
        $cautions = Caution::orderByDesc('created_at')->paginate(25);
        return view('caution.admin', compact('cautions'));
    }

    // Back-office capture
    public function adminCapture(Caution $caution)
    {
        // middleware 'is_admin' protège cette route
        Stripe::setApiKey(config('services.stripe.secret'));
        try {
            $pi = PaymentIntent::retrieve($caution->stripe_payment_intent_id);
            $pi->capture();
            $caution->update(['status' => 'captured']);
            return redirect()->back()->with('success','Caution capturée.');
        } catch (\Exception $e) {
            Log::error('Admin capture error: '.$e->getMessage());
            return redirect()->back()->with('error','Erreur capture : '.$e->getMessage());
        }
    }

    // Back-office cancel
    public function adminCancel(Caution $caution)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        try {
            $pi = PaymentIntent::retrieve($caution->stripe_payment_intent_id);
            $pi->cancel();
            $caution->update(['status' => 'canceled']);
            return redirect()->back()->with('info','Caution annulée.');
        } catch (\Exception $e) {
            Log::error('Admin cancel error: '.$e->getMessage());
            return redirect()->back()->with('error','Erreur annulation : '.$e->getMessage());
        }
    }

}
