<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Mail\AbandonedCartMail;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:abandoned-cart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends email reminders for abandoned carts/orders older than 1 hour.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $abandonedOrders = Order::where('status', 'pending')
            ->where('abandoned_email_sent', false)
            ->where('created_at', '<=', now()->subHour())
            ->where('created_at', '>=', now()->subDays(2)) // Don't send for very old orders
            ->whereNotNull('user_id') // We need a user to email to
            ->with('user', 'event')
            ->get();

        $count = 0;
        foreach ($abandonedOrders as $order) {
            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)->send(new AbandonedCartMail($order));
                
                $order->update(['abandoned_email_sent' => true]);
                $count++;
            }
        }

        $this->info("Sent {$count} abandoned cart emails.");
    }
}
