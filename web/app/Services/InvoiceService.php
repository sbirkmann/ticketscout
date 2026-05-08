<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceService
{
    /**
     * Generate Vendor to Customer Invoice.
     */
    public function generateVendorToCustomerInvoice(Order $order): Invoice
    {
        $vendor = $order->event->vendor;
        $customerName = $order->user_id ? $order->user->name : $order->guest_name;
        
        $net = 0;
        $tax = 0;
        
        // Calculate totals based on items
        foreach ($order->items as $item) {
            $taxRate = $item->ticketCategory ? $item->ticketCategory->tax_rate : 0;
            $itemNet = $item->price / (1 + ($taxRate / 100));
            $itemTax = $item->price - $itemNet;
            
            $net += ($itemNet * $item->quantity);
            $tax += ($itemTax * $item->quantity);
        }
        
        $gross = $order->total_amount;
        
        $settings = $vendor->vendorSettings ?? $vendor->vendorSettings()->create();
        $prefix = $settings->invoice_prefix ?? 'RE-';
        $number = $settings->invoice_next_number ?? 1;
        
        $invoiceNumber = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
        
        $settings->increment('invoice_next_number');

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'user_id' => $order->user_id, // can be null for guests
            'type' => 'vendor_to_customer',
            'invoice_number' => $invoiceNumber,
            'net' => $net,
            'tax' => $tax,
            'gross' => $gross,
        ]);

        $pdfHtml = view('invoices.vendor_to_customer', compact('order', 'invoice', 'vendor', 'customerName', 'settings'))->render();
        $pdf = Pdf::loadHTML($pdfHtml);
        
        $path = "invoices/{$invoice->id}.pdf";
        Storage::disk('local')->put($path, $pdf->output());
        
        $invoice->update(['pdf_path' => $path]);

        return $invoice;
    }

    /**
     * Generate Platform to Vendor Invoice for fees.
     */
    public function generatePlatformToVendorInvoice(Order $order): Invoice
    {
        $vendor = $order->event->vendor;
        $fee = $order->platform_fee;
        
        // Assuming platform fee is 19% tax included (example)
        $taxRate = 19;
        $net = $fee / (1 + ($taxRate / 100));
        $tax = $fee - $net;

        $invoiceNumber = 'FEE-' . strtoupper(Str::random(8));

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'type' => 'platform_to_vendor',
            'invoice_number' => $invoiceNumber,
            'net' => $net,
            'tax' => $tax,
            'gross' => $fee,
        ]);

        $pdfHtml = view('invoices.platform_to_vendor', compact('order', 'invoice', 'vendor'))->render();
        $pdf = Pdf::loadHTML($pdfHtml);
        
        $path = "invoices/{$invoice->id}.pdf";
        Storage::disk('local')->put($path, $pdf->output());
        
        $invoice->update(['pdf_path' => $path]);

        return $invoice;
    }
}
