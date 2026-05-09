<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Event;
use App\Models\Voucher;
use Stripe\StripeClient;

class CheckoutController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $event->load(['ticketCategories', 'addons', 'vendor.vendorSettings', 'location']);

        // Parse cart from GET (sent by Event/Show.vue)
        $rawTickets = json_decode($request->get('tickets', '{}'), true) ?? [];
        $rawAddons  = json_decode($request->get('addons', '{}'), true)  ?? [];

        $groupReservation = null;
        $resaleListing = null;

        if ($request->has('group_token')) {
            $groupReservation = \App\Models\GroupReservation::where('share_token', $request->get('group_token'))
                ->where('status', 'open')
                ->first();
            
            if ($groupReservation) {
                $cat = $event->ticketCategories->where('name', $groupReservation->ticket_category)->first();
                if ($cat) {
                    $rawTickets = [$cat->id => 1];
                }
            }
        } elseif ($request->has('listing_id')) {
            $resaleListing = \App\Models\TicketListing::where('id', $request->get('listing_id'))
                ->where('status', 'active')
                ->first();
            
            if ($resaleListing) {
                $cat = $resaleListing->ticket->ticketCategory;
                if ($cat) {
                    $rawTickets = [$cat->id => 1];
                }
            }
        }

        // Build cart items
        $cartItems   = [];
        $subtotal    = 0;

        foreach ($rawTickets as $catId => $qty) {
            if ((int)$qty <= 0) continue;
            $cat = $event->ticketCategories->firstWhere('id', (int)$catId);
            if (!$cat) continue;
            
            $price = floatval($cat->price);
            if ($groupReservation) {
                $price = floatval($groupReservation->price_per_ticket);
            } elseif ($resaleListing) {
                $price = floatval($resaleListing->asking_price);
            }

            $lineTotal = round($price * (int)$qty, 2);
            $subtotal += $lineTotal;
            $cartItems[] = [
                'type'     => 'ticket',
                'id'       => $cat->id,
                'name'     => $cat->name,
                'price'    => $price,
                'qty'      => (int)$qty,
                'total'    => $lineTotal,
                'resale_listing_id' => $resaleListing?->id,
            ];
        }

        foreach ($rawAddons as $addonId => $qty) {
            if ((int)$qty <= 0) continue;
            $addon = $event->addons->firstWhere('id', (int)$addonId);
            if (!$addon) continue;
            $lineTotal = round(floatval($addon->price) * (int)$qty, 2);
            $subtotal += $lineTotal;
            $cartItems[] = [
                'type'     => 'addon',
                'id'       => $addon->id,
                'name'     => $addon->name . ' (Add-on)',
                'price'    => floatval($addon->price),
                'qty'      => (int)$qty,
                'total'    => $lineTotal,
            ];
        }

        // Tax from vendor settings
        $vendorSettings = $event->vendor?->vendorSettings;
        $taxRate       = $vendorSettings?->tax_rate ?? 19.0;
        $taxExempt     = $vendorSettings?->tax_exempt ?? false;
        $pricesInclude = $vendorSettings?->prices_include_tax ?? true;

        if ($taxExempt) {
            $taxAmount = 0;
            $netAmount = $subtotal;
        } elseif ($pricesInclude) {
            // prices are gross (Brutto)
            $taxAmount = round($subtotal - ($subtotal / (1 + $taxRate / 100)), 2);
            $netAmount = round($subtotal - $taxAmount, 2);
        } else {
            // prices are net
            $taxAmount = round($subtotal * $taxRate / 100, 2);
            $netAmount = $subtotal;
            $subtotal  = $netAmount + $taxAmount;
        }

        // Platform fee (5%)
        $platformFee = round($subtotal * 0.05, 2);

        // Check if event has a linked seating plan (via location)
        $seatingPlan = null;
        if ($event->location_id) {
            $plan = \App\Models\SeatingPlan::where('location_id', $event->location_id)
                ->with(['rows.seats'])
                ->first();
            if ($plan) {
                $seatingPlan = $plan;
            }
        }

        return Inertia::render('Checkout/Index', [
            'event'       => $event,
            'cartItems'   => $cartItems,
            'subtotal'    => $subtotal,
            'netAmount'   => $netAmount,
            'taxAmount'   => $taxAmount,
            'taxRate'     => $taxRate,
            'taxExempt'   => $taxExempt,
            'platformFee' => $platformFee,
            'stripeKey'   => config('services.stripe.key'),
            'seatingPlan' => $seatingPlan,
            'groupToken'  => $request->get('group_token'),
            'groupPrice'  => $groupReservation?->price_per_ticket,
            'listingId'   => $request->get('listing_id'),
        ]);
    }

    public function process(Request $request, Event $event)
    {
        $validated = $request->validate([
            'checkout_type'     => 'required|in:guest,login,register',
            'billing_first_name'=> 'required|string|max:100',
            'billing_last_name' => 'required|string|max:100',
            'billing_company'   => 'nullable|string|max:150',
            'billing_street'    => 'required|string|max:200',
            'billing_zip'       => 'required|string|max:20',
            'billing_city'      => 'required|string|max:100',
            'billing_country'   => 'required|string|max:3',
            'billing_phone'     => 'nullable|string|max:30',
            'guest_email'       => 'required_if:checkout_type,guest|email|nullable',
            'agb_accepted'      => 'required|accepted',
            'voucher_code'      => 'nullable|string',
            'promo_code'        => 'nullable|string',
            'cart'              => 'required|array',
            'subtotal'          => 'required|numeric',
            'tax_rate'          => 'required|numeric',
            'tax_amount'        => 'required|numeric',
            'is_gift'           => 'nullable|boolean',
            'gift_recipient_name' => 'nullable|string|max:100',
            'gift_message'      => 'nullable|string|max:1000',
            'use_loyalty_points'=> 'nullable|integer|min:0',
            'group_token'       => 'nullable|string|exists:group_reservations,share_token',
            'listing_id'        => 'nullable|integer|exists:ticket_listings,id',
        ]);

        $event->load(['ticketCategories', 'addons', 'vendor.vendorSettings', 'location']);

        $totalAmount = floatval($validated['subtotal']);
        $voucherUsed = 0;
        $voucherId   = null;
        $promoDiscount = 0;
        $promoId = null;

        // Apply Promo Code first
        if (!empty($validated['promo_code'])) {
            $promoCodeObj = \App\Models\PromoCode::where('code', $validated['promo_code'])
                ->where(function ($q) use ($event) {
                    $q->where('vendor_id', $event->vendor_id)
                      ->orWhereNull('vendor_id'); // Global Gift Cards
                })
                ->first();

            if ($promoCodeObj && $promoCodeObj->isValidForEvent($event->id)) {
                $promoDiscount = round($promoCodeObj->calculateDiscount($totalAmount), 2);
                $totalAmount = max(0, $totalAmount - $promoDiscount);
                $promoId = $promoCodeObj->id;
            }
        }

        // Apply voucher if provided
        if (!empty($validated['voucher_code'])) {
            $voucher = Voucher::where('code', $validated['voucher_code'])
                ->where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->first();

            if ($voucher && $voucher->balance > 0) {
                $voucherUsed = min($voucher->balance, $totalAmount);
                $totalAmount = max(0, $totalAmount - $voucherUsed);
                $voucherId   = $voucher->id;
            }
        }

        // Apply Loyalty Points (100 points = 1 EUR)
        $loyaltyPointsUsed = 0;
        $loyaltyDiscount = 0;
        if (!empty($validated['use_loyalty_points']) && auth()->check()) {
            $user = auth()->user();
            $pointsToUse = min(intval($validated['use_loyalty_points']), $user->loyalty_points);
            
            if ($pointsToUse > 0) {
                $potentialDiscount = round($pointsToUse / 100, 2);
                $loyaltyDiscount = min($potentialDiscount, $totalAmount);
                $loyaltyPointsUsed = intval($loyaltyDiscount * 100); // Only deduct what was actually used
                
                $totalAmount = max(0, $totalAmount - $loyaltyDiscount);
            }
        }

        $platformFee = round($totalAmount * 0.05, 2);

        // If nothing left to pay after voucher/promo/loyalty
        if ($totalAmount <= 0) {
            return $this->completeOrderWithoutPayment(
                $event, $validated, $voucherId, $voucherUsed, $promoId, $promoDiscount, $loyaltyPointsUsed, $loyaltyDiscount
            );
        }

        // Save Abandoned Cart
        $email = $validated['guest_email'] ?? auth()->user()?->email;
        if ($email) {
            \App\Models\AbandonedCart::updateOrCreate(
                ['email' => $email, 'event_id' => $event->id],
                ['status' => 'pending', 'cart_data' => $validated['cart'], 'updated_at' => now()]
            );
        }

        // Create Stripe PaymentIntent (Platform MoR model - platform collects all)
        $stripe = new StripeClient(config('services.stripe.secret'));

        $intentData = [
            'amount'                     => intval($totalAmount * 100),
            'currency'                   => 'eur',
            'automatic_payment_methods'  => ['enabled' => true],
            'metadata' => [
                'event_id'          => $event->id,
                'checkout_type'     => $validated['checkout_type'],
                'billing_email'     => $validated['guest_email'] ?? auth()->user()?->email,
                'voucher_id'        => $voucherId,
                'voucher_amount'    => $voucherUsed,
                'promo_id'          => $promoId,
                'promo_amount'      => $promoDiscount,
            ],
        ];

        // If vendor has connected a Stripe account, transfer funds automatically minus our platform fee
        if ($event->vendor?->vendorSettings?->stripe_account_id) {
            $platformFeeCents = intval($platformFee * 100);
            $intentData['transfer_data'] = [
                'destination' => $event->vendor->vendorSettings->stripe_account_id,
            ];
            $intentData['application_fee_amount'] = $platformFeeCents;
        }

        $paymentIntent = $stripe->paymentIntents->create($intentData);

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
            'orderData'    => array_merge($validated, [
                'voucher_id'     => $voucherId,
                'voucher_amount' => $voucherUsed,
                'promo_id'       => $promoId,
                'promo_amount'   => $promoDiscount,
                'loyalty_points_used' => $loyaltyPointsUsed,
                'loyalty_discount'    => $loyaltyDiscount,
                'total_charged'  => $totalAmount,
                'platform_fee'   => $platformFee,
            ])
        ]);
    }

    public function validatePromo(Request $request, Event $event)
    {
        $validated = $request->validate([
            'promo_code' => 'required|string',
            'subtotal'   => 'required|numeric'
        ]);

        $promo = \App\Models\PromoCode::where('code', $validated['promo_code'])
            ->where(function ($q) use ($event) {
                $q->where('vendor_id', $event->vendor_id)
                  ->orWhereNull('vendor_id'); // Global Gift Cards
            })
            ->first();

        if (!$promo || !$promo->isValidForEvent($event->id)) {
            return response()->json(['valid' => false, 'message' => 'Gutscheincode ungültig oder abgelaufen.']);
        }

        $discount = round($promo->calculateDiscount($validated['subtotal']), 2);

        return response()->json([
            'valid' => true,
            'discount' => $discount,
            'code' => $promo->code,
            'message' => 'Gutschein erfolgreich angewendet!'
        ]);
    }

    public function complete(Request $request, Event $event)
    {
        $validated = $request->validate([
            'paymentIntentId' => 'required|string',
            'orderData'       => 'required|array',
        ]);

        $orderData = $validated['orderData'];
        $event->load(['ticketCategories', 'addons', 'vendor']);

        // Verify payment with Stripe
        $stripe = new StripeClient(config('services.stripe.secret'));
        $intent = $stripe->paymentIntents->retrieve($validated['paymentIntentId']);

        if ($intent->status !== 'succeeded') {
            return back()->withErrors(['payment' => 'Zahlung konnte nicht bestätigt werden.']);
        }

        $order = $this->createOrder($event, $orderData, $validated['paymentIntentId']);

        return redirect()->route('checkout.success')->with('order_id', $order->id);
    }

    private function completeOrderWithoutPayment($event, $orderData, $voucherId, $voucherUsed, $promoId, $promoDiscount, $loyaltyPointsUsed = 0, $loyaltyDiscount = 0)
    {
        $order = $this->createOrder($event, array_merge($orderData, [
            'voucher_id'     => $voucherId,
            'voucher_amount' => $voucherUsed,
            'promo_id'       => $promoId,
            'promo_amount'   => $promoDiscount,
            'loyalty_points_used' => $loyaltyPointsUsed,
            'loyalty_discount'    => $loyaltyDiscount,
            'total_charged'  => 0,
            'platform_fee'   => 0,
        ]), null);

        return redirect()->route('checkout.success')->with('order_id', $order->id);
    }

    private function createOrder($event, $orderData, $paymentIntentId)
    {
        $subtotal  = floatval($orderData['subtotal'] ?? 0);
        $taxAmount = floatval($orderData['tax_amount'] ?? 0);
        $taxRate   = floatval($orderData['tax_rate'] ?? 19.0);
        $platformFee = floatval($orderData['platform_fee'] ?? round($subtotal * 0.05, 2));

        $order = \App\Models\Order::create([
            'user_id'             => auth()->id() ?? null,
            'event_id'            => $event->id,
            'guest_email'         => $orderData['guest_email'] ?? auth()->user()?->email,
            'billing_first_name'  => $orderData['billing_first_name'],
            'billing_last_name'   => $orderData['billing_last_name'],
            'billing_company'     => $orderData['billing_company'] ?? null,
            'billing_street'      => $orderData['billing_street'],
            'billing_zip'         => $orderData['billing_zip'],
            'billing_city'        => $orderData['billing_city'],
            'billing_country'     => $orderData['billing_country'] ?? 'DE',
            'billing_phone'       => $orderData['billing_phone'] ?? null,
            'total_amount'        => $subtotal,
            'promo_code_id'       => $orderData['promo_id'] ?? null,
            'promo_discount'      => $orderData['promo_amount'] ?? 0,
            'platform_fee'        => $platformFee,
            'tax_rate'            => $taxRate,
            'tax_amount'          => $taxAmount,
            'voucher_id'          => $orderData['voucher_id'] ?? null,
            'voucher_amount_used' => $orderData['voucher_amount'] ?? 0,
            'is_gift'             => $orderData['is_gift'] ?? false,
            'gift_recipient_name' => $orderData['gift_recipient_name'] ?? null,
            'gift_message'        => $orderData['gift_message'] ?? null,
            'affiliate_link_id'   => session('affiliate_ref'),
            'stripe_payment_intent_id' => $paymentIntentId,
            'status'              => 'paid',
            'agb_accepted'        => true,
        ]);

        // Process cart items
        foreach ($orderData['cart'] as $item) {
            if ($item['type'] === 'ticket') {
                $cat = $event->ticketCategories->firstWhere('id', $item['id']);
                if ($cat) {
                    \App\Models\OrderItem::create([
                        'order_id'           => $order->id,
                        'ticket_category_id' => $cat->id,
                        'quantity'           => $item['qty'],
                        'price'              => $item['price'],
                    ]);
                    // Increment sold count
                    $cat->increment('sold', $item['qty']);
                }
            }
        }

        // Deduct voucher balance
        if (!empty($orderData['voucher_id']) && $orderData['voucher_amount'] > 0) {
            $voucher = Voucher::find($orderData['voucher_id']);
            if ($voucher) {
                $voucher->balance = max(0, $voucher->balance - $orderData['voucher_amount']);
                if ($voucher->balance <= 0) $voucher->is_active = false;
                $voucher->save();
            }
        }
        
        // Increase usage of promo code
        if (!empty($orderData['promo_id'])) {
            $promo = \App\Models\PromoCode::find($orderData['promo_id']);
            if ($promo) {
                $promo->increment('current_uses');
            }
        }

        // Handle Loyalty Points
        if ($order->user_id) {
            $user = \App\Models\User::find($order->user_id);
            if ($user) {
                // Deduct used points
                if (!empty($orderData['loyalty_points_used']) && $orderData['loyalty_points_used'] > 0) {
                    $user->loyalty_points = max(0, $user->loyalty_points - $orderData['loyalty_points_used']);
                }
                
                // Award new points (e.g., 5 points per 1 EUR spent)
                // Using total_charged so they only get points for what they actually pay
                $chargedAmount = floatval($orderData['total_charged'] ?? $subtotal);
                if ($chargedAmount > 0) {
                    $pointsEarned = intval($chargedAmount * 5);
                    $user->loyalty_points += $pointsEarned;
                }
                
                $user->save();
            }
        }

        // Recover Abandoned Cart if exists
        $email = $orderData['guest_email'] ?? auth()->user()?->email;
        if ($email) {
            \App\Models\AbandonedCart::where('email', $email)
                ->where('event_id', $event->id)
                ->update(['status' => 'recovered']);
        }

        // Handle Group Reservation
        if (!empty($orderData['group_token'])) {
            $reservation = \App\Models\GroupReservation::where('share_token', $orderData['group_token'])->first();
            if ($reservation) {
                $participants = $reservation->participants ?? [];
                $participants[] = [
                    'email' => $email,
                    'order_id' => $order->id,
                    'paid_at' => now()->toDateTimeString(),
                ];
                $reservation->participants = $participants;
                
                // Check if complete
                if (count($participants) >= $reservation->total_tickets) {
                    $reservation->status = 'complete';
                }
                $reservation->save();
            }
        }

        // Handle Resale Listings
        if (!empty($orderData['listing_id'])) {
            $listing = \App\Models\TicketListing::find($orderData['listing_id']);
            if ($listing && $listing->status === 'active') {
                $listing->update(['status' => 'sold', 'buyer_id' => $order->user_id ?? null]);
                
                // Invalidate the original ticket
                $originalTicket = $listing->ticket;
                $originalTicket->update(['status' => 'resold']);
                
                // Note: The new ticket for the buyer is created by the standard loop below
                // because the item is in the cart.
            }
        }

        // Generate tickets and invoice
        try {
            $ticketService = new \App\Services\TicketService();
            $ticketService->generateTicketsForOrder($order);

            $invoiceService = new \App\Services\InvoiceService();
            $invoiceService->generateVendorToCustomerInvoice($order);
        } catch (\Exception $e) {
            // Non-fatal - log but don't block
            \Log::error('Post-order services failed: ' . $e->getMessage());
        }

        return $order;
    }
}
