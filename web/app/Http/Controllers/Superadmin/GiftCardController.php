<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PromoCode;
use Inertia\Inertia;
use Illuminate\Support\Str;

class GiftCardController extends Controller
{
    public function index()
    {
        $giftCards = PromoCode::whereNull('vendor_id')
            ->whereNull('event_id')
            ->latest()
            ->get();

        return Inertia::render('Admin/GiftCards/Index', [
            'giftCards' => $giftCards
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|unique:promo_codes',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(Str::random(10));
        }

        PromoCode::create([
            'vendor_id' => null,
            'event_id' => null,
            'code' => $validated['code'],
            'type' => $validated['type'],
            'value' => $validated['value'],
            'max_uses' => $validated['max_uses'],
            'expires_at' => $validated['expires_at'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Gutschein erfolgreich erstellt.');
    }

    public function destroy(PromoCode $giftCard)
    {
        if ($giftCard->vendor_id !== null) {
            abort(403);
        }
        
        $giftCard->delete();
        return back()->with('success', 'Gutschein gelöscht.');
    }
}
