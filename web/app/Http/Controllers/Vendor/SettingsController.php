<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\VendorSetting;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = auth()->user()->vendorSettings()->firstOrCreate(
            ['vendor_id' => auth()->id()]
        );

        return Inertia::render('Vendor/Settings', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $settings = auth()->user()->vendorSettings()->firstOrCreate(
            ['vendor_id' => auth()->id()]
        );

        $validated = $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
            'invoice_prefix' => 'required|string|max:20',
            'invoice_footer_text' => 'nullable|string',
            'disable_invoicing' => 'boolean',
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string',
            'tax_number' => 'nullable|string|max:100',
            'vat_id' => 'nullable|string|max:100',
            'email_template' => 'nullable|string',
            'sender_name' => 'nullable|string|max:255',
            'ticket_html' => 'nullable|string',
            'ticket_css' => 'nullable|string',
            'custom_domain' => 'nullable|string|max:255',
        ]);

        $settings->update($request->only([
            'tax_rate', 'invoice_prefix', 'invoice_footer_text', 'disable_invoicing',
            'company_name', 'company_address', 'tax_number', 'vat_id',
            'iban', 'bic', 'bank_name', 'email_template', 'sender_name', 'custom_domain'
        ]));

        // Update default ticket template
        $template = auth()->user()->ticketTemplates()->firstOrCreate(
            ['vendor_id' => auth()->id()],
            ['name' => 'Standard']
        );
        $template->update([
            'html_content' => $request->input('ticket_html', $template->html_content),
            'css_content' => $request->input('ticket_css', $template->css_content)
        ]);

        return back()->with('success', 'Einstellungen erfolgreich gespeichert.');
    }
}
