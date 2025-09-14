<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Webhook;

class AbonnementController extends Controller
{

    public function index() {
        return view('abonnement.activation');
    }
    public function createCheckout()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $user = User::first(); 

        if (!$user->stripe_customer_id) {
            $customer = Customer::create([
                'email' => $user->email,
                'name'  => $user->name,
            ]);
            $user->stripe_customer_id = $customer->id;
            $user->save();
        }

        $checkoutSession = Session::create([
            'customer' => $user->stripe_customer_id,
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => 'price_1S5VrCEQIR7wMWWKtzUFluQP',
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            // 'subscription_data' => [
            //     'trial_period_days' => 7,
            // ],
            'success_url' => route('checkout.success'),
            'cancel_url'  => route('checkout.cancel'),
        ]);

        return redirect($checkoutSession->url);
    }

    public function success()
    {
        return view('abonnement.success');
    }

    public function cancel()
    {
        return view('abonnement.cancel');
    }

    public function webhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $user = User::where('stripe_customer_id', $session->customer)->first();

            if ($user && isset($session->subscription)) {
                $stripeSub = Subscription::retrieve($session->subscription);

                Abonnement::updateOrCreate(
                    ['stripe_subscription_id' => $stripeSub->id],
                    [
                        'user_id' => $user->id,
                        'stripe_price_id' => $stripeSub->items->data[0]->price->id,
                        'status' => $stripeSub->status,
                        'trial_ends_at' => $stripeSub->trial_end ? Carbon::createFromTimestamp($stripeSub->trial_end) : null,
                        'ends_at' => null,
                    ]
                );
            }
        }

        if ($event->type === 'invoice.payment_succeeded') {
            $invoice = $event->data->object;
            $sub = Abonnement::where('stripe_subscription_id', $invoice->subscription)->first();
            if ($sub) {
                $sub->status = 'active';
                $sub->save();
            }
        }

        if ($event->type === 'customer.subscription.deleted') {
            $stripeSub = $event->data->object;
            $sub = Abonnement::where('stripe_subscription_id', $stripeSub->id)->first();
            if ($sub) {
                $sub->status = 'canceled';
                $sub->ends_at = now();
                $sub->save();
            }
        }

        return response()->json(['status' => 'success']);
    }
}
