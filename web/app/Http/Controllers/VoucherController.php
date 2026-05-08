<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;

class VoucherController extends Controller
{
    public function check(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $voucher = Voucher::where('code', $request->code)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->first();

        if (!$voucher || $voucher->balance <= 0) {
            return response()->json(['valid' => false]);
        }

        return response()->json([
            'valid'   => true,
            'code'    => $voucher->code,
            'balance' => $voucher->balance,
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:10|max:1000',
            'message' => 'nullable|string|max:500',
        ]);

        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card', 'paypal', 'giropay'], // Depending on your stripe config
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Ticketsout24 Geschenkgutschein',
                        'description' => $validated['message'] ?? 'Wertgutschein für alle Events',
                    ],
                    'unit_amount' => intval($validated['amount'] * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('voucher.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('gutscheine'),
            'metadata' => [
                'type' => 'voucher',
                'amount' => $validated['amount'],
                'message' => $validated['message'] ?? '',
                'user_id' => auth()->id() ?? '',
            ],
        ]);

        return response()->json(['url' => $session->url]);
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');
        if (!$sessionId) return redirect()->route('gutscheine');

        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        $session = $stripe->checkout->sessions->retrieve($sessionId);

        if ($session->payment_status !== 'paid') {
            return redirect()->route('gutscheine')->withErrors(['payment' => 'Zahlung nicht erfolgreich.']);
        }

        // Prevent duplicate fulfillment
        if (Voucher::where('stripe_payment_id', $session->payment_intent)->exists()) {
            return \Inertia\Inertia::render('Static/VoucherSuccess', ['alreadyProcessed' => true]);
        }

        $amount = $session->metadata->amount;
        $message = $session->metadata->message;
        $userId = $session->metadata->user_id ?: null;

        // Generate unique code (e.g., TX-ABC123XYZ)
        $code = 'TX-' . strtoupper(\Illuminate\Support\Str::random(9));

        $voucher = Voucher::create([
            'code' => $code,
            'initial_balance' => $amount,
            'balance' => $amount,
            'is_active' => true,
            'stripe_payment_id' => $session->payment_intent,
        ]);

        // Create an Order to keep accounting clean for the platform
        $order = \App\Models\Order::create([
            'user_id'             => $userId,
            'event_id'            => null,
            'guest_email'         => $session->customer_details->email,
            'billing_first_name'  => $session->customer_details->name ?? 'Gutschein',
            'billing_last_name'   => 'Käufer',
            'billing_street'      => 'Nicht angegeben',
            'billing_zip'         => '00000',
            'billing_city'        => 'Nicht angegeben',
            'total_amount'        => $amount,
            'platform_fee'        => 0, // platform keeps 100%
            'tax_rate'            => 0, // Vouchers are usually multi-purpose = 0% until redeemed
            'tax_amount'          => 0,
            'stripe_payment_intent_id' => $session->payment_intent,
            'status'              => 'paid',
            'agb_accepted'        => true,
        ]);

        // In a real app, send email with $voucher code to $session->customer_details->email

        return \Inertia\Inertia::render('Static/VoucherSuccess', [
            'voucherCode' => $voucher->code,
            'amount' => $amount,
            'email' => $session->customer_details->email
        ]);
    }}
