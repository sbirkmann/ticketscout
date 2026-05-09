<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Order;
use App\Mail\EventReminderMail;
use Illuminate\Support\Facades\Mail;

#[Signature('orders:event-reminders')]
#[Description('Sends reminder emails 24 hours before the event starts.')]
class SendEventReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::where('status', 'paid')
            ->where('reminder_sent', false)
            ->whereHas('event', function($q) {
                // Event starts in the next 24 hours, but hasn't started yet
                $q->where('start_date', '<=', now()->addHours(24))
                  ->where('start_date', '>', now());
            })
            ->whereNotNull('user_id')
            ->with(['user', 'event.location', 'tickets'])
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)->send(new EventReminderMail($order));
                
                $order->update(['reminder_sent' => true]);
                $count++;
            }
        }

        $this->info("Sent {$count} event reminders.");
    }
}
