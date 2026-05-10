<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PosArticle;
use App\Models\PosArticleCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosArticleController extends Controller
{
    public function index()
    {
        $categories = PosArticleCategory::where('vendor_id', auth()->id())->orderBy('sort_order')->get();
        $articles = PosArticle::where('vendor_id', auth()->id())->with('category')->latest()->get();

        return Inertia::render('Vendor/PosArticles/Index', [
            'categories' => $categories,
            'articles' => $articles
        ]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer'
        ]);

        PosArticleCategory::create([
            'vendor_id' => auth()->id(),
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#e2e8f0',
            'sort_order' => $validated['sort_order'] ?? 0
        ]);

        return back()->with('success', 'Kategorie erstellt.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:pos_article_categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'default_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        PosArticle::create([
            'vendor_id' => auth()->id(),
            ...$validated
        ]);

        return back()->with('success', 'Artikel erstellt.');
    }

    public function update(Request $request, PosArticle $pos_article)
    {
        if ($pos_article->vendor_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'category_id' => 'nullable|exists:pos_article_categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'default_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $pos_article->update($validated);

        return back()->with('success', 'Artikel aktualisiert.');
    }

    public function destroy(PosArticle $pos_article)
    {
        if ($pos_article->vendor_id !== auth()->id()) abort(403);
        $pos_article->delete();
        return back()->with('success', 'Artikel gelöscht.');
    }
    
    public function destroyCategory(PosArticleCategory $pos_article_category)
    {
        if ($pos_article_category->vendor_id !== auth()->id()) abort(403);
        $pos_article_category->delete();
        return back()->with('success', 'Kategorie gelöscht.');
    }
}
