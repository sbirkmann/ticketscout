<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AffiliateLink;
use App\Models\Event;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AffiliateLinkController extends Controller
{
    public function index()
    {
        $vendorId = Auth::id();
        
        $links = AffiliateLink::where('vendor_id', $vendorId)
            ->with('event')
            ->withCount('orders')
            ->latest()
            ->get();
            
        $events = Event::where('vendor_id', $vendorId)->select('id', 'title')->get();

        return Inertia::render('Vendor/Affiliate/Index', [
            'links' => $links,
            'events' => $events
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'event_id' => 'nullable|exists:events,id',
            'code' => 'nullable|string|unique:affiliate_links,code|max:50',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Str::slug($validated['name']) . '-' . Str::random(5);
        }

        AffiliateLink::create([
            'vendor_id' => Auth::id(),
            'event_id' => $validated['event_id'],
            'name' => $validated['name'],
            'code' => $validated['code'],
            'clicks' => 0,
        ]);

        return back()->with('success', 'Affiliate Link erstellt.');
    }

    public function destroy(AffiliateLink $affiliate_link)
    {
        if ($affiliate_link->vendor_id !== Auth::id()) {
            abort(403);
        }
        
        $affiliate_link->delete();
        return back()->with('success', 'Link gelöscht.');
    }
}
