<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\Order;
use App\Models\Payout;
use Carbon\Carbon;

class GenerateVendorPayouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payouts:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate pending payouts for vendors after their events have ended';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting payout generation...');

        // Find events that ended yesterday and haven't been processed yet
        // A simple way is to check if a payout reference containing the event ID exists
        $events = Event::where('status', 'published')
            ->whereDate('end_date', Carbon::yesterday()->toDateString())
            ->orWhere(function ($query) {
                $query->whereNull('end_date')
                      ->whereDate('start_date', Carbon::yesterday()->toDateString());
            })
            ->get();

        $count = 0;
        foreach ($events as $event) {
            // Check if payout already generated for this event
            $reference = 'EVENT-' . $event->id;
            if (Payout::where('reference', $reference)->exists()) {
                continue;
            }

            // Calculate total revenue for this event from paid orders
            $revenue = Order::where('event_id', $event->id)
                ->where('status', 'paid')
                ->sum('total_amount');

            if ($revenue <= 0) {
                continue;
            }

            // Calculate platform fee (e.g. 5%)
            $platformFee = $revenue * 0.05;
            $payoutAmount = $revenue - $platformFee;

            Payout::create([
                'vendor_id' => $event->vendor_id,
                'amount' => $payoutAmount,
                'status' => 'pending',
                'reference' => $reference,
            ]);

            $this->info("Generated payout for event {$event->title} (ID: {$event->id}) - Amount: {$payoutAmount}");
            $count++;
        }

        $this->info("Generated {$count} new payouts.");
    }
}
