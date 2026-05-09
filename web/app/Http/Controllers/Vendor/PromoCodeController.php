<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class PromoCodeController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::with('event')
            ->where('vendor_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Vendor/PromoCodes/Index', [
            'promoCodes' => $promoCodes
        ]);
    }

    public function create()
    {
        $events = Event::where('vendor_id', auth()->id())->get(['id', 'title']);
        return Inertia::render('Vendor/PromoCodes/Create', [
            'events' => $events
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('promo_codes')->where('vendor_id', auth()->id())],
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0.01',
            'event_id' => 'nullable|exists:events,id',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ]);

        if (!empty($validated['event_id'])) {
            $event = Event::findOrFail($validated['event_id']);
            if ($event->vendor_id !== auth()->id()) abort(403);
        }

        $validated['vendor_id'] = auth()->id();
        PromoCode::create($validated);

        return redirect()->route('vendor.promo-codes.index')->with('success', 'Gutschein-Code erstellt.');
    }

    public function edit(PromoCode $promoCode)
    {
        if ($promoCode->vendor_id !== auth()->id()) abort(403);
        
        $events = Event::where('vendor_id', auth()->id())->get(['id', 'title']);
        return Inertia::render('Vendor/PromoCodes/Edit', [
            'promoCode' => $promoCode,
            'events' => $events
        ]);
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        if ($promoCode->vendor_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('promo_codes')->where('vendor_id', auth()->id())->ignore($promoCode->id)],
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0.01',
            'event_id' => 'nullable|exists:events,id',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['event_id'])) {
            $event = Event::findOrFail($validated['event_id']);
            if ($event->vendor_id !== auth()->id()) abort(403);
        }

        $promoCode->update($validated);

        return redirect()->route('vendor.promo-codes.index')->with('success', 'Gutschein-Code aktualisiert.');
    }

    public function destroy(PromoCode $promoCode)
    {
        if ($promoCode->vendor_id !== auth()->id()) abort(403);
        
        $promoCode->delete();
        return redirect()->route('vendor.promo-codes.index')->with('success', 'Gutschein-Code gelöscht.');
    }
}
