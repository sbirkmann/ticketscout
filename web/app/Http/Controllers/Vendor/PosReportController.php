<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PosShift;
use App\Models\PosReceipt;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosReportController extends Controller
{
    public function index()
    {
        // Get all shifts for terminals owned by this vendor
        $shifts = PosShift::whereHas('terminal', function($q) {
            $q->where('vendor_id', auth()->id());
        })
        ->with(['terminal', 'openedBy'])
        ->orderBy('opened_at', 'desc')
        ->paginate(20);

        return Inertia::render('Vendor/PosReports/Index', [
            'shifts' => $shifts
        ]);
    }

    public function showShift(PosShift $pos_shift)
    {
        if ($pos_shift->terminal->vendor_id !== auth()->id()) abort(403);

        $pos_shift->load(['terminal', 'openedBy', 'cashTransactions']);
        
        $receipts = PosReceipt::where('pos_shift_id', $pos_shift->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Vendor/PosReports/Show', [
            'shift' => $pos_shift,
            'receipts' => $receipts
        ]);
    }
}
