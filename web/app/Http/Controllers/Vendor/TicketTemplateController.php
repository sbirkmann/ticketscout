<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\TicketTemplate;

class TicketTemplateController extends Controller
{
    public function index()
    {
        $templates = auth()->user()->ticketTemplates()->latest()->get();
        return Inertia::render('Vendor/Templates/Index', ['templates' => $templates]);
    }

    public function create()
    {
        return Inertia::render('Vendor/Templates/Builder');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'html_content' => 'nullable|string',
            'css_content' => 'nullable|string',
            'layout_data' => 'nullable|string',
        ]);

        auth()->user()->ticketTemplates()->create($validated);

        return redirect()->route('vendor.templates.index')->with('success', 'Template saved.');
    }

    public function edit(TicketTemplate $template)
    {
        if ($template->vendor_id !== auth()->id()) abort(403);
        return Inertia::render('Vendor/Templates/Builder', ['template' => $template]);
    }

    public function update(Request $request, TicketTemplate $template)
    {
        if ($template->vendor_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'html_content' => 'nullable|string',
            'css_content' => 'nullable|string',
            'layout_data' => 'nullable|string',
        ]);

        $template->update($validated);

        return redirect()->route('vendor.templates.index')->with('success', 'Template updated.');
    }

    public function destroy(TicketTemplate $template)
    {
        if ($template->vendor_id !== auth()->id()) abort(403);
        $template->delete();
        return redirect()->route('vendor.templates.index')->with('success', 'Template deleted.');
    }
}
