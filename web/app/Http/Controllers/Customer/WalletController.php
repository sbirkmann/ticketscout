<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Ticket;
use App\Models\WalletTransaction;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function show(Ticket $ticket)
    {
        // Must belong to auth user's order
        if ($ticket->order->user_id !== Auth::id()) {
            abort(403);
        }

        // Must have wallet enabled
        if (!$ticket->event->enable_wallet) {
            abort(404, 'Guthaben-System ist für dieses Event nicht aktiviert.');
        }

        $transactions = WalletTransaction::where('ticket_id', $ticket->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Customer/Wallet/Show', [
            'ticket' => $ticket->load('ticketCategory', 'event'),
            'transactions' => $transactions
        ]);
    }

    public function topup(Request $request, Ticket $ticket)
    {
        if ($ticket->order->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:5|max:500' // min 5 EUR, max 500
        ]);

        $amount = (float) $validated['amount'];

        $stripe = new StripeClient(config('services.stripe.secret'));

        $sessionData = [
            'payment_method_types' => ['card', 'paypal', 'giropay'], // Assuming available
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Guthaben-Aufladung: ' . $ticket->event->title,
                        'description' => 'Ticket #' . substr($ticket->code, 0, 8),
                    ],
                    'unit_amount' => intval($amount * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('wallet.success', $ticket->id) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('wallet.show', $ticket->id),
            'metadata' => [
                'ticket_id' => $ticket->id,
                'topup_amount' => $amount,
                'type' => 'wallet_topup'
            ]
        ];

        // Transfer directly to vendor if they have an account connected
        $vendorSettings = $ticket->event->vendor?->vendorSettings;
        if ($vendorSettings && $vendorSettings->stripe_account_id) {
            $sessionData['payment_intent_data'] = [
                'transfer_data' => [
                    'destination' => $vendorSettings->stripe_account_id,
                ],
                // Maybe take a platform fee? For top-ups, we might not take a fee or take a fixed % 
                // Let's just pass it 100% to vendor for now or apply the same global fee.
                // We'll skip platform fee on top-ups for simplicity, or vendor pays standard stripe fees.
            ];
        }

        $checkoutSession = $stripe->checkout->sessions->create($sessionData);

        return Inertia::location($checkoutSession->url);
    }

    public function success(Request $request, Ticket $ticket)
    {
        if ($ticket->order->user_id !== Auth::id()) {
            abort(403);
        }

        $sessionId = $request->get('session_id');
        if (!$sessionId) {
            return redirect()->route('wallet.show', $ticket->id)->with('error', 'Ungültige Sitzung.');
        }

        $stripe = new StripeClient(config('services.stripe.secret'));
        $session = $stripe->checkout->sessions->retrieve($sessionId);

        if ($session->payment_status === 'paid') {
            // Check if already processed
            $alreadyProcessed = WalletTransaction::where('ticket_id', $ticket->id)
                ->where('description', 'LIKE', "%$sessionId%")
                ->exists();

            if (!$alreadyProcessed) {
                $amount = (float) $session->metadata->topup_amount;

                // Create transaction
                WalletTransaction::create([
                    'ticket_id' => $ticket->id,
                    'type' => 'topup',
                    'amount' => $amount,
                    'description' => 'Online Aufladung via Stripe (Session: ' . $sessionId . ')'
                ]);

                // Update balance
                $ticket->wallet_balance += $amount;
                $ticket->save();

                return redirect()->route('wallet.show', $ticket->id)->with('success', 'Dein Guthaben wurde erfolgreich aufgeladen!');
            }
        }

        return redirect()->route('wallet.show', $ticket->id);
    }
}
