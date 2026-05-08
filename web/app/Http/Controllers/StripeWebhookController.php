<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Webhook;
use App\Models\Order;
use App\Models\Event;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        $event = null;

        try {
            if ($endpoint_secret) {
                $event = Webhook::constructEvent(
                    $payload, $sig_header, $endpoint_secret
                );
            } else {
                $event = json_decode($payload);
            }
        } catch(\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type == 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            $this->handlePaymentIntentSucceeded($paymentIntent);
        }

        return response()->json(['status' => 'success']);
    }

    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        // Try to find an order with this payment intent
        $order = Order::where('stripe_payment_intent_id', $paymentIntent->id)->first();
        
        if ($order) {
            if ($order->status !== 'paid') {
                $order->update(['status' => 'paid', 'payment_status' => 'paid']);
                // Generate tickets and invoice if not generated yet
                if ($order->tickets()->count() === 0) {
                    try {
                        $ticketService = new \App\Services\TicketService();
                        $ticketService->generateTicketsForOrder($order);
            
                        $invoiceService = new \App\Services\InvoiceService();
                        $invoiceService->generateVendorToCustomerInvoice($order);
                    } catch (\Exception $e) {
                        Log::error('Stripe Webhook Ticket/Invoice generation failed: ' . $e->getMessage());
                    }
                }
            }
        } else {
            Log::info("PaymentIntent {$paymentIntent->id} succeeded but no order found yet. The user might still be on the return_url.");
        }
    }
}
