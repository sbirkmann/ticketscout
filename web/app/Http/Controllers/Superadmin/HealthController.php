<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\User;
use App\Models\Event;

class HealthController extends Controller
{
    public function index()
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];
        
        if (File::exists($logPath)) {
            // Get last 100 lines of log
            $file = new \SplFileObject($logPath, 'r');
            $file->seek(PHP_INT_MAX);
            $lastLine = $file->key();
            $lines = new \LimitIterator($file, max(0, $lastLine - 100), $lastLine);
            foreach ($lines as $line) {
                if (!empty(trim($line))) {
                    $logs[] = $line;
                }
            }
            $logs = array_reverse($logs); // Newest first
        }

        $stats = [
            'total_users' => User::count(),
            'total_vendors' => User::role('vendor')->count(),
            'total_events' => Event::count(),
            'total_orders' => Order::count(),
            'orders_paid' => Order::where('status', 'paid')->count(),
            'orders_failed' => Order::where('status', 'failed')->count(),
            'db_size' => $this->getDbSize()
        ];

        return Inertia::render('Superadmin/Health/Index', [
            'logs' => $logs,
            'stats' => $stats
        ]);
    }

    private function getDbSize()
    {
        // Simple SQLite DB size if using SQLite
        $connection = config('database.default');
        if ($connection === 'sqlite') {
            $path = config('database.connections.sqlite.database');
            return File::exists($path) ? round(File::size($path) / 1024 / 1024, 2) . ' MB' : 'N/A';
        }
        
        return 'N/A (Not SQLite)';
    }
}
