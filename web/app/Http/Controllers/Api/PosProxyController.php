<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PosPrintJob;
use App\Models\PosTerminal;

class PosProxyController extends Controller
{
    /**
     * Authenticate terminal via api_key
     */
    private function authenticateTerminal(Request $request)
    {
        // Simple auth for proxy: you need to pass a terminal_id and api_key
        // In reality, we might use a dedicated api_token column in pos_terminals.
        // For now, let's just use the terminal_id and the vendor's user ID as a simple secret, or just trust the terminal_id if an api_key matches something.
        // Let's add api_key logic: if not present, we will just use terminal_id for this basic implementation.
        
        $terminalId = $request->input('terminal_id');
        $apiKey = $request->header('X-API-KEY'); // e.g. "pos-proxy-secret"

        if (!$terminalId || $apiKey !== config('app.key')) {
            abort(401, 'Unauthorized');
        }

        return PosTerminal::findOrFail($terminalId);
    }

    public function getPrintJobs(Request $request)
    {
        $terminal = $this->authenticateTerminal($request);

        $jobs = PosPrintJob::where('pos_terminal_id', $terminal->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get();

        return response()->json([
            'jobs' => $jobs
        ]);
    }

    public function updateJobStatus(Request $request, PosPrintJob $job)
    {
        $terminal = $this->authenticateTerminal($request);

        if ($job->pos_terminal_id !== $terminal->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:printed,failed'
        ]);

        $job->update([
            'status' => $validated['status'],
            'printed_at' => $validated['status'] === 'printed' ? now() : null
        ]);

        return response()->json(['success' => true]);
    }

    public function syncTransactions(Request $request)
    {
        // For the POS Local Hub
        $hubKey = $request->header('X-HUB-KEY');
        if ($hubKey !== config('app.key')) {
            abort(401, 'Unauthorized Hub');
        }

        $lastId = $request->input('last_id', 0);
        
        $transactions = \App\Models\PosReceipt::where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->take(500)
            ->get(['id', 'receipt_number', 'total_gross', 'payment_method', 'created_at']);

        return response()->json([
            'transactions' => $transactions
        ]);
    }
}
