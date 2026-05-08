<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    public function connect(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('vendor')) {
            abort(403);
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        if (!$user->stripe_account_id) {
            $account = $stripe->accounts->create([
                'type' => 'express',
                'email' => $user->email,
            ]);

            $user->stripe_account_id = $account->id;
            $user->save();
        }

        $accountLink = $stripe->accountLinks->create([
            'account' => $user->stripe_account_id,
            'refresh_url' => route('stripe.connect'),
            'return_url' => route('vendor.dashboard'),
            'type' => 'account_onboarding',
        ]);

        return redirect($accountLink->url);
    }
}
