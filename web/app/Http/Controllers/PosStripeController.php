<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\StripeClient;
use App\Models\PosTerminal;
use Illuminate\Support\Facades\Session;

class PosStripeController extends Controller
{
    private function getStripeClient($vendorId)
    {
        $vendor = \App\Models\User::find($vendorId);
        // Assuming vendor has a stripe_account_id if Stripe Connect is used
        // Or fallback to default platform keys. For Terminal, Stripe Connect is typical.
        return new StripeClient([
            'api_key' => config('services.stripe.secret'),
            'stripe_version' => '2023-10-16',
        ]);
    }

    public function connectionToken(Request $request)
    {
        $terminalId = Session::get('pos_terminal_id');
        if (!$terminalId) abort(403);
        $terminal = PosTerminal::findOrFail($terminalId);

        $stripe = $this->getStripeClient($terminal->vendor_id);
        
        $vendor = \App\Models\User::find($terminal->vendor_id);
        
        // If using Stripe Connect
        $options = [];
        if ($vendor->stripe_account_id) {
            $options['stripe_account'] = $vendor->stripe_account_id;
        }

        try {
            $connectionToken = $stripe->terminal->connectionTokens->create([], $options);
            return response()->json(['secret' => $connectionToken->secret]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createPaymentIntent(Request $request)
    {
        $terminalId = Session::get('pos_terminal_id');
        if (!$terminalId) abort(403);
        $terminal = PosTerminal::findOrFail($terminalId);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.5',
            'currency' => 'required|string|size:3'
        ]);

        $stripe = $this->getStripeClient($terminal->vendor_id);
        $vendor = \App\Models\User::find($terminal->vendor_id);

        $options = [];
        if ($vendor->stripe_account_id) {
            $options['stripe_account'] = $vendor->stripe_account_id;
        }

        try {
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => (int)($validated['amount'] * 100),
                'currency' => strtolower($validated['currency']),
                'payment_method_types' => ['card_present'],
                'capture_method' => 'manual',
            ], $options);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'id' => $paymentIntent->id
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function capturePaymentIntent(Request $request)
    {
        $terminalId = Session::get('pos_terminal_id');
        if (!$terminalId) abort(403);
        $terminal = PosTerminal::findOrFail($terminalId);

        $validated = $request->validate([
            'payment_intent_id' => 'required|string'
        ]);

        $stripe = $this->getStripeClient($terminal->vendor_id);
        $vendor = \App\Models\User::find($terminal->vendor_id);

        $options = [];
        if ($vendor->stripe_account_id) {
            $options['stripe_account'] = $vendor->stripe_account_id;
        }

        try {
            $intent = $stripe->paymentIntents->capture($validated['payment_intent_id'], [], $options);
            return response()->json(['success' => true, 'status' => $intent->status]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
