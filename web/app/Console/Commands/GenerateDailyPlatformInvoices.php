<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateDailyPlatformInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily collective platform fee invoices for all vendors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $yesterday = Carbon::yesterday();

        $this->info("Generating platform invoices for " . $yesterday->format('Y-m-d'));

        // Get all vendors who had successful orders yesterday
        $vendors = User::role('vendor')->whereHas('events.orders', function($q) use ($yesterday) {
            $q->where('status', 'paid')->whereDate('created_at', $yesterday);
        })->get();

        foreach ($vendors as $vendor) {
            $orders = Order::whereHas('event', function($q) use ($vendor) {
                    $q->where('vendor_id', $vendor->id);
                })
                ->where('status', 'paid')
                ->whereDate('created_at', $yesterday)
                ->get();

            $totalFees = $orders->sum('platform_fee');

            if ($totalFees <= 0) continue;

            $taxRate = 19;
            $net = $totalFees / (1 + ($taxRate / 100));
            $tax = $totalFees - $net;

            $invoiceNumber = 'FEE-' . $yesterday->format('Ymd') . '-' . $vendor->id;

            $invoice = Invoice::create([
                'vendor_id' => $vendor->id,
                'type' => 'platform_to_vendor',
                'invoice_number' => $invoiceNumber,
                'net' => $net,
                'tax' => $tax,
                'gross' => $totalFees,
            ]);

            // Using the existing platform_to_vendor view but adapting it for multiple orders
            // Wait, the existing view might expect a single $order. We will pass a dummy order or adapt the view.
            // For now let's just pass the orders collection to the view.
            
            $pdfHtml = view('invoices.platform_to_vendor_daily', [
                'invoice' => $invoice,
                'vendor' => $vendor,
                'orders' => $orders,
                'date' => $yesterday
            ])->render();
            
            $pdf = Pdf::loadHTML($pdfHtml);
            
            $path = "invoices/{$invoice->id}.pdf";
            Storage::disk('local')->put($path, $pdf->output());
            
            $invoice->update(['pdf_path' => $path]);

            $this->info("Generated invoice {$invoiceNumber} for Vendor {$vendor->name} (Total: {$totalFees})");
        }

        $this->info("Done.");
    }
}
