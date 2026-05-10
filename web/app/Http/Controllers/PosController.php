<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PosTerminal;
use App\Models\Ticket;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class PosController extends Controller
{
    public function showLogin()
    {
        if (Session::has('pos_terminal_id')) {
            return redirect()->route('pos.dashboard');
        }

        return Inertia::render('Pos/Login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login_code' => 'required|string',
            'pin' => 'required|string'
        ]);

        $terminal = PosTerminal::where('login_code', strtoupper($validated['login_code']))->first();

        if ($terminal && Hash::check($validated['pin'], $terminal->pin)) {
            if (!$terminal->is_active) {
                return back()->withErrors(['login_code' => 'Dieses Terminal ist deaktiviert.']);
            }

            Session::put('pos_terminal_id', $terminal->id);
            return redirect()->route('pos.dashboard');
        }

        return back()->withErrors(['login_code' => 'Ungültiger Code oder PIN.']);
    }

    public function logout()
    {
        Session::forget('pos_terminal_id');
        return redirect()->route('pos.login');
    }

    public function dashboard()
    {
        $terminalId = Session::get('pos_terminal_id');
        if (!$terminalId) {
            return redirect()->route('pos.login');
        }

        $terminal = PosTerminal::findOrFail($terminalId);
        
        // Check for open shift
        $openShift = \App\Models\PosShift::where('pos_terminal_id', $terminal->id)
            ->whereNull('closed_at')
            ->first();

        if (!$openShift) {
            // Need to open a shift. Fetch active events for this vendor.
            $activeEvents = \App\Models\Event::where('vendor_id', $terminal->vendor_id)
                ->where('status', 'published')
                ->where('is_approved', true)
                ->orderBy('start_date', 'asc')
                ->get();
                
            return Inertia::render('Pos/OpenShift', [
                'terminal' => $terminal,
                'events' => $activeEvents
            ]);
        }

        // Fetch articles for the event associated with this shift (if we associate shift with event... wait, we need to associate shift with event or just terminal? The receipt is associated with an event. Let's add event_id to pos_shifts if we want. Actually we can just pass the active event ID in the session, or the cashier selects it from the dashboard, but if we do it at shift opening, it's better.)
        // But pos_shifts doesn't have event_id in migration. Let's see migration:
        // pos_shifts: terminal_id, opened_by, opened_at, closed_at, starting_cash, ending_cash, expected_cash, difference
        // We will just let the user select the event in the OpenShift screen and store it in session, or add event_id to pos_shifts. 
        // Let's just add event_id to pos_shifts dynamically or store in session. 
        $eventId = Session::get('pos_event_id');
        if (!$eventId) {
             // fallback: just pick the first published event or null
             $event = \App\Models\Event::where('vendor_id', $terminal->vendor_id)->where('status', 'published')->first();
             if($event) {
                 $eventId = $event->id;
                 Session::put('pos_event_id', $eventId);
             }
        }

        $event = $eventId ? \App\Models\Event::find($eventId) : null;

        // Fetch articles
        $articles = [];
        if ($event) {
            $eventArticles = \App\Models\EventPosArticle::where('event_id', $event->id)->get()->keyBy('pos_article_id');
            $vendorArticlesQuery = \App\Models\PosArticle::where('vendor_id', $terminal->vendor_id)
                ->where('is_active', true)
                ->with('category')
                ->get();
                
            foreach ($vendorArticlesQuery as $va) {
                if ($eventArticles->has($va->id)) {
                    $ea = $eventArticles->get($va->id);
                    if ($ea->is_available) {
                        $price = $ea->override_price !== null ? $ea->override_price : $va->default_price;
                        $articles[] = [
                            'id' => $va->id,
                            'name' => $va->name,
                            'sku' => $va->sku,
                            'price' => (float) $price,
                            'tax_rate' => (float) $va->tax_rate,
                            'category' => $va->category ? $va->category->name : null,
                            'color' => $va->category ? $va->category->color : '#e2e8f0',
                        ];
                    }
                }
            }
        }

        $vendorSettings = \App\Models\VendorSetting::where('vendor_id', $terminal->vendor_id)->first();
        
        $receiptSettings = [
            'header' => $event && $event->pos_receipt_header_override ? $event->pos_receipt_header_override : ($vendorSettings?->pos_receipt_header ?? ''),
            'footer' => $event && $event->pos_receipt_footer_override ? $event->pos_receipt_footer_override : ($vendorSettings?->pos_receipt_footer ?? 'Vielen Dank für Ihren Besuch!'),
            'company_name' => $vendorSettings?->company_name ?? 'Vendor',
        ];

        $cashiers = \App\Models\PosCashier::where('vendor_id', $terminal->vendor_id)->get(['id', 'name', 'pin']);

        return Inertia::render('Pos/Dashboard', [
            'terminal' => $terminal,
            'shift' => $openShift,
            'event' => $event,
            'articles' => $articles,
            'receiptSettings' => $receiptSettings,
            'cashiers' => $cashiers
        ]);
    }

    public function openShift(Request $request)
    {
        $terminalId = Session::get('pos_terminal_id');
        if (!$terminalId) abort(403);
        $terminal = PosTerminal::findOrFail($terminalId);

        $validated = $request->validate([
            'starting_cash' => 'required|numeric|min:0',
            'event_id' => 'required|exists:events,id'
        ]);

        $shift = \App\Models\PosShift::create([
            'pos_terminal_id' => $terminal->id,
            // opened_by can be null if not using individual cashier logins, but we can use an 'operator_name' or just leave it null
            'opened_at' => now(),
            'starting_cash' => $validated['starting_cash'],
            'expected_cash' => clone $validated['starting_cash'] // initially same
        ]);

        Session::put('pos_event_id', $validated['event_id']);

        return redirect()->route('pos.dashboard')->with('success', 'Schicht eröffnet.');
    }

    public function closeShift(Request $request)
    {
        $terminalId = Session::get('pos_terminal_id');
        if (!$terminalId) abort(403);
        $terminal = PosTerminal::findOrFail($terminalId);

        $openShift = \App\Models\PosShift::where('pos_terminal_id', $terminal->id)
            ->whereNull('closed_at')
            ->firstOrFail();

        $validated = $request->validate([
            'ending_cash' => 'required|numeric|min:0'
        ]);

        // Calculate expected cash
        // expected = starting_cash + sum(cash receipts) + sum(deposits) - sum(withdrawals)
        $cashReceiptsSum = \App\Models\PosReceipt::where('pos_shift_id', $openShift->id)
            ->where('payment_method', 'cash')
            ->where('status', 'paid')
            ->sum('total_gross');
            
        $deposits = \App\Models\PosCashTransaction::where('pos_shift_id', $openShift->id)->where('type', 'deposit')->sum('amount');
        $withdrawals = \App\Models\PosCashTransaction::where('pos_shift_id', $openShift->id)->where('type', 'withdrawal')->sum('amount');

        $expectedCash = $openShift->starting_cash + $cashReceiptsSum + $deposits - $withdrawals;
        $difference = $validated['ending_cash'] - $expectedCash;

        $openShift->update([
            'closed_at' => now(),
            'ending_cash' => $validated['ending_cash'],
            'expected_cash' => $expectedCash,
            'difference' => $difference
        ]);

        Session::forget('pos_event_id');

        return redirect()->route('pos.dashboard')->with('success', 'Schicht beendet. Z-Bon wurde erstellt.');
    }

    public function fetchTicket(Request $request)
    {
        $terminalId = Session::get('pos_terminal_id');
        if (!$terminalId) abort(403);

        $terminal = PosTerminal::findOrFail($terminalId);

        $validated = $request->validate([
            'code' => 'required|string'
        ]);

        // Find ticket
        $ticket = Ticket::with(['event', 'ticketCategory', 'order.user'])
            ->where('code', $validated['code'])
            ->first();

        if (!$ticket) {
            return response()->json(['error' => 'Ticket nicht gefunden.'], 404);
        }

        if (!$ticket->event->enable_wallet) {
            return response()->json(['error' => 'Für dieses Event ist das Guthaben-System nicht aktiviert.'], 400);
        }

        // Must belong to the same vendor
        if ($ticket->event->vendor_id !== $terminal->vendor_id) {
            return response()->json(['error' => 'Ticket gehört nicht zu diesem Veranstalter.'], 403);
        }

        return response()->json([
            'ticket' => [
                'id' => $ticket->id,
                'code' => $ticket->code,
                'attendee_name' => $ticket->attendee_name,
                'wallet_balance' => (float) $ticket->wallet_balance,
                'category' => $ticket->ticketCategory?->name,
                'event' => $ticket->event->title
            ]
        ]);
    }

    public function charge(Request $request, Ticket $ticket)
    {
        $terminalId = Session::get('pos_terminal_id');
        if (!$terminalId) abort(403);
        $terminal = PosTerminal::findOrFail($terminalId);

        if ($ticket->event->vendor_id !== $terminal->vendor_id) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string'
        ]);

        $amount = (float) $validated['amount'];

        if ($ticket->wallet_balance < $amount) {
            return response()->json(['error' => 'Nicht genügend Guthaben. Aktuell: ' . number_format($ticket->wallet_balance, 2) . ' €'], 400);
        }

        // Deduct balance
        $ticket->wallet_balance -= $amount;
        $ticket->save();

        // Create transaction
        WalletTransaction::create([
            'ticket_id' => $ticket->id,
            'pos_terminal_id' => $terminal->id,
            'type' => 'charge',
            'amount' => $amount,
            'description' => $validated['description'] ?: 'Abbuchung: ' . $terminal->name
        ]);

        return response()->json([
            'success' => true,
            'new_balance' => (float) $ticket->wallet_balance,
            'message' => 'Erfolgreich abgebucht!'
        ]);
    }

    public function checkout(Request $request)
    {
        $terminalId = Session::get('pos_terminal_id');
        if (!$terminalId) abort(403);
        $terminal = PosTerminal::findOrFail($terminalId);

        $shift = \App\Models\PosShift::where('pos_terminal_id', $terminal->id)
            ->whereNull('closed_at')
            ->firstOrFail();

        $validated = $request->validate([
            'payment_method' => 'required|in:cash,wallet,card',
            'ticket_code' => 'required_if:payment_method,wallet|nullable|string',
            'payment_reference' => 'nullable|string',
            'tip_amount' => 'nullable|numeric|min:0',
            'pos_cashier_id' => 'nullable|exists:pos_cashiers,id',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:pos_articles,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0',
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $terminal, $shift, $request) {
            $totalGross = 0;
            $totalNet = 0;
            $taxDetails = [];

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['price'] * $item['qty'];
                $totalGross += $lineTotal;

                $taxRate = $item['tax_rate'];
                $net = $lineTotal / (1 + ($taxRate / 100));
                $tax = $lineTotal - $net;
                $totalNet += $net;

                if (!isset($taxDetails[(string)$taxRate])) {
                    $taxDetails[(string)$taxRate] = ['net' => 0, 'tax' => 0, 'gross' => 0];
                }
                $taxDetails[(string)$taxRate]['net'] += $net;
                $taxDetails[(string)$taxRate]['tax'] += $tax;
                $taxDetails[(string)$taxRate]['gross'] += $lineTotal;
            }

            $tipAmount = isset($validated['tip_amount']) ? (float)$validated['tip_amount'] : 0;
            $finalCharge = $totalGross + $tipAmount;

            // Handle Payment
            $paymentReference = $validated['payment_reference'] ?? null;
            if ($validated['payment_method'] === 'wallet') {
                $ticket = Ticket::where('code', $validated['ticket_code'])->lockForUpdate()->first();
                if (!$ticket || $ticket->wallet_balance < $finalCharge) {
                    return response()->json(['error' => 'Nicht genügend Guthaben oder Ticket ungültig.'], 400);
                }
                
                $ticket->wallet_balance -= $finalCharge;
                $ticket->save();

                WalletTransaction::create([
                    'ticket_id' => $ticket->id,
                    'pos_terminal_id' => $terminal->id,
                    'type' => 'charge',
                    'amount' => $finalCharge,
                    'description' => 'Kasseneinkauf' . ($tipAmount > 0 ? ' inkl. Trinkgeld' : '')
                ]);
                
                $paymentReference = $ticket->code;
            }

            // Next receipt number (GoBD Compliance: Gapless sequential numbering)
            $lastReceipt = \App\Models\PosReceipt::where('vendor_id', $terminal->vendor_id)
                ->lockForUpdate()
                ->orderBy('receipt_number', 'desc')
                ->first();
                
            $receiptNumber = $lastReceipt ? $lastReceipt->receipt_number + 1 : 1000;

            // Optional dummy signature for future TSE (Technische Sicherheitseinrichtung)
            $tseSignature = hash('sha256', $receiptNumber . $finalCharge . $terminal->id . time() . config('app.key'));

            $receipt = \App\Models\PosReceipt::create([
                'vendor_id' => $terminal->vendor_id,
                'event_id' => Session::get('pos_event_id'),
                'pos_terminal_id' => $terminal->id,
                'pos_shift_id' => $shift->id,
                'pos_cashier_id' => $validated['pos_cashier_id'] ?? null,
                'receipt_number' => $receiptNumber,
                'total_gross' => $totalGross, // the receipt is technically only the goods amount
                'tip_amount' => $tipAmount,
                'total_net' => $totalNet,
                'tax_details' => $taxDetails,
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $paymentReference,
                'status' => 'paid',
                // 'tse_signature' => $tseSignature // If we had this column
            ]);

            foreach ($validated['items'] as $item) {
                $article = \App\Models\PosArticle::find($item['id']);
                \App\Models\PosReceiptItem::create([
                    'pos_receipt_id' => $receipt->id,
                    'pos_article_id' => $article->id,
                    'name' => $article->name,
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'tax_rate' => $item['tax_rate'],
                    'total' => $item['price'] * $item['qty']
                ]);
            }

            // Queue the print job for the optional Local Proxy App
            // We'll generate a minimal HTML string from the receipt data.
            $vendorSettings = \App\Models\VendorSetting::where('vendor_id', $terminal->vendor_id)->first();
            $event = \App\Models\Event::find(Session::get('pos_event_id'));
            
            $headerText = $event && $event->pos_receipt_header_override ? $event->pos_receipt_header_override : ($vendorSettings?->pos_receipt_header ?? '');
            $footerText = $event && $event->pos_receipt_footer_override ? $event->pos_receipt_footer_override : ($vendorSettings?->pos_receipt_footer ?? 'Vielen Dank für Ihren Besuch!');
            $companyName = $vendorSettings?->company_name ?? 'Vendor';
            
            $itemsHtml = '';
            foreach ($validated['items'] as $item) {
                $itemsHtml .= "<tr>
                    <td class='align-top py-1'>{$item['qty']}x</td>
                    <td class='align-top py-1 px-2'>{$item['name']}</td>
                    <td class='align-top py-1 text-right'>" . number_format($item['price'] * $item['qty'], 2, ',', '.') . " €</td>
                </tr>";
            }

            $htmlContent = "
                <div class='text-center mb-4'>
                    <div class='font-bold text-lg mb-2'>{$companyName}</div>
                    <div class='whitespace-pre-line'>{$headerText}</div>
                </div>
                <div class='border-b border-black border-dashed pb-2 mb-2 text-center'>
                    <div class='font-bold'>Kaufbeleg</div>
                    <div>Beleg-Nr: {$receiptNumber}</div>
                    <div>Datum: " . now()->format('d.m.Y H:i') . "</div>
                    <div>Kasse: {$terminal->name}</div>
                </div>
                <table class='w-full mb-2'><tbody>{$itemsHtml}</tbody></table>
                <div class='border-t border-black border-dashed pt-2 mb-2'>
                    <div class='flex justify-between font-bold text-sm'>
                        <span>Gesamt (Brutto)</span>
                        <span>" . number_format($totalGross, 2, ',', '.') . " €</span>
                    </div>
                </div>
                <div class='mb-4 text-xs'>
                    <div class='flex justify-between font-bold mt-2'>
                        <span>Gegeben: " . strtoupper($validated['payment_method']) . "</span>
                        <span>" . number_format($totalGross, 2, ',', '.') . " €</span>
                    </div>
                </div>
                <div class='text-center mt-6 pt-4 border-t border-black border-dashed whitespace-pre-line'>
                    {$footerText}
                </div>
            ";

            \App\Models\PosPrintJob::create([
                'pos_terminal_id' => $terminal->id,
                'pos_receipt_id' => $receipt->id,
                'html_content' => $htmlContent,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'receipt' => $receipt->load('items')
            ]);
        });
    }

    public function recentReceipts($terminalId)
    {
        $terminal = PosTerminal::findOrFail($terminalId);

        $shift = \App\Models\PosShift::where('pos_terminal_id', $terminal->id)
            ->whereNull('closed_at')
            ->first();

        if (!$shift) {
            return response()->json(['receipts' => []]);
        }

        $receipts = \App\Models\PosReceipt::with(['items', 'terminal'])
            ->where('pos_shift_id', $shift->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json(['receipts' => $receipts]);
    }

    public function refundReceipt(Request $request, $receiptId)
    {
        $originalReceipt = \App\Models\PosReceipt::with('items')->findOrFail($receiptId);
        
        // Ensure it's not already refunded
        $alreadyRefunded = \App\Models\PosReceipt::where('refund_for_receipt_id', $originalReceipt->id)->exists();
        if ($alreadyRefunded || $originalReceipt->status !== 'paid') {
            return response()->json(['error' => 'Beleg wurde bereits storniert oder ist ungültig.'], 400);
        }

        $terminal = PosTerminal::findOrFail($originalReceipt->pos_terminal_id);

        $shift = \App\Models\PosShift::where('pos_terminal_id', $terminal->id)
            ->whereNull('closed_at')
            ->first();

        if (!$shift) {
            return response()->json(['error' => 'Keine offene Schicht am Terminal gefunden. Storno nicht möglich.'], 400);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($originalReceipt, $terminal, $shift) {
            // Next receipt number
            $lastReceipt = \App\Models\PosReceipt::where('vendor_id', $terminal->vendor_id)
                ->lockForUpdate()
                ->orderBy('receipt_number', 'desc')
                ->first();
                
            $receiptNumber = $lastReceipt ? $lastReceipt->receipt_number + 1 : 1000;

            // Invert tax details
            $taxDetails = $originalReceipt->tax_details;
            if (is_array($taxDetails)) {
                foreach ($taxDetails as $rate => $amounts) {
                    $taxDetails[$rate]['net'] = -$amounts['net'];
                    $taxDetails[$rate]['tax'] = -$amounts['tax'];
                    $taxDetails[$rate]['gross'] = -$amounts['gross'];
                }
            }

            // Create refund receipt
            $refundReceipt = \App\Models\PosReceipt::create([
                'vendor_id' => $originalReceipt->vendor_id,
                'event_id' => $originalReceipt->event_id,
                'pos_terminal_id' => $terminal->id,
                'pos_shift_id' => $shift->id,
                'pos_cashier_id' => request('pos_cashier_id'),
                'refund_for_receipt_id' => $originalReceipt->id,
                'receipt_number' => $receiptNumber,
                'total_gross' => -$originalReceipt->total_gross,
                'tip_amount' => -$originalReceipt->tip_amount,
                'total_net' => -$originalReceipt->total_net,
                'tax_details' => $taxDetails,
                'payment_method' => $originalReceipt->payment_method,
                'payment_reference' => $originalReceipt->payment_reference,
                'status' => 'refunded'
            ]);

            // Copy items with negative quantities
            foreach ($originalReceipt->items as $item) {
                \App\Models\PosReceiptItem::create([
                    'pos_receipt_id' => $refundReceipt->id,
                    'pos_article_id' => $item->pos_article_id,
                    'name' => 'STORNO: ' . $item->name,
                    'quantity' => -$item->quantity,
                    'price' => $item->price,
                    'tax_rate' => $item->tax_rate,
                    'total' => -$item->total
                ]);
            }

            // Refund Wallet if applicable
            if ($originalReceipt->payment_method === 'wallet' && $originalReceipt->payment_reference) {
                $ticket = \App\Models\Ticket::where('code', $originalReceipt->payment_reference)->lockForUpdate()->first();
                if ($ticket) {
                    $refundAmount = $originalReceipt->total_gross + $originalReceipt->tip_amount;
                    $ticket->wallet_balance += $refundAmount;
                    $ticket->save();

                    \App\Models\WalletTransaction::create([
                        'ticket_id' => $ticket->id,
                        'pos_terminal_id' => $terminal->id,
                        'type' => 'credit',
                        'amount' => $refundAmount,
                        'description' => 'Storno Kasseneinkauf (Beleg ' . $originalReceipt->receipt_number . ')'
                    ]);
                }
            }

            // Change original status
            $originalReceipt->status = 'refunded';
            $originalReceipt->save();

            return response()->json([
                'success' => true,
                'receipt' => $refundReceipt->load('items')
            ]);
        });
    }
}
