<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PosReceipt;
use Illuminate\Support\Facades\Response;

class PosExportController extends Controller
{
    /**
     * Export POS Receipts for external systems (e.g. Vectron, DATEV)
     */
    public function exportCsv(Request $request)
    {
        // Require API Key authentication
        $apiKey = $request->header('X-API-KEY');
        if ($apiKey !== config('app.key')) {
            abort(401, 'Unauthorized');
        }

        $vendorId = $request->input('vendor_id');
        if (!$vendorId) {
            abort(400, 'vendor_id required');
        }

        $receipts = PosReceipt::with('items')
            ->where('vendor_id', $vendorId)
            ->where('status', 'paid')
            ->orderBy('created_at', 'asc')
            ->get();

        $csvData = "ReceiptNumber,Date,Terminal,PaymentMethod,TotalGross,TotalNet,Items\n";

        foreach ($receipts as $receipt) {
            $itemsString = $receipt->items->map(function ($item) {
                return $item->quantity . 'x ' . $item->name;
            })->implode(' | ');

            $csvData .= sprintf(
                "%s,%s,%s,%s,%s,%s,\"%s\"\n",
                $receipt->receipt_number,
                $receipt->created_at->toDateTimeString(),
                $receipt->pos_terminal_id,
                $receipt->payment_method,
                number_format($receipt->total_gross, 2, '.', ''),
                number_format($receipt->total_net, 2, '.', ''),
                str_replace('"', '""', $itemsString)
            );
        }

        return Response::make($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pos_export_' . date('Y-m-d') . '.csv"',
        ]);
    }

    public function exportJson(Request $request)
    {
        $apiKey = $request->header('X-API-KEY');
        if ($apiKey !== config('app.key')) {
            abort(401, 'Unauthorized');
        }

        $vendorId = $request->input('vendor_id');
        if (!$vendorId) {
            abort(400, 'vendor_id required');
        }

        $receipts = PosReceipt::with('items')
            ->where('vendor_id', $vendorId)
            ->where('status', 'paid')
            ->orderBy('created_at', 'asc')
            ->paginate(100);

        return response()->json($receipts);
    }
}
