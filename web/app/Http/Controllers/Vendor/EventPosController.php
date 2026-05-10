<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\PosArticle;
use App\Models\EventPosArticle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventPosController extends Controller
{
    public function show(Event $event)
    {
        if ($event->vendor_id !== auth()->id()) abort(403);

        $vendorArticles = PosArticle::where('vendor_id', auth()->id())
            ->where('is_active', true)
            ->with('category')
            ->get();
            
        $eventArticles = EventPosArticle::where('event_id', $event->id)->get();

        return Inertia::render('Vendor/Events/PosSettings', [
            'event' => $event,
            'vendorArticles' => $vendorArticles,
            'eventArticles' => $eventArticles
        ]);
    }

    public function update(Request $request, Event $event)
    {
        if ($event->vendor_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'pos_receipt_header_override' => 'nullable|string',
            'pos_receipt_footer_override' => 'nullable|string',
            'articles' => 'array',
            'articles.*.pos_article_id' => 'required|exists:pos_articles,id',
            'articles.*.override_price' => 'nullable|numeric|min:0',
            'articles.*.is_available' => 'required|boolean',
        ]);

        $event->update([
            'pos_receipt_header_override' => $validated['pos_receipt_header_override'] ?? null,
            'pos_receipt_footer_override' => $validated['pos_receipt_footer_override'] ?? null,
        ]);

        // Sync event articles
        EventPosArticle::where('event_id', $event->id)->delete();

        if (isset($validated['articles'])) {
            foreach ($validated['articles'] as $articleData) {
                EventPosArticle::create([
                    'event_id' => $event->id,
                    'pos_article_id' => $articleData['pos_article_id'],
                    'override_price' => $articleData['override_price'],
                    'is_available' => $articleData['is_available']
                ]);
            }
        }

        return back()->with('success', 'POS-Einstellungen für Event gespeichert.');
    }
}
