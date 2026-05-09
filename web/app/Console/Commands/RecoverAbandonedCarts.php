<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AbandonedCart;
use App\Mail\AbandonedCartMail;
use Illuminate\Support\Facades\Mail;

class RecoverAbandonedCarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carts:recover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send recovery emails for abandoned carts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting abandoned cart recovery...');

        // Find carts abandoned more than 1 hour ago but less than 24 hours ago, and not yet recovered
        $carts = AbandonedCart::where('status', 'pending')
            ->where('updated_at', '<', now()->subHour())
            ->where('updated_at', '>', now()->subHours(24))
            ->get();

        $count = 0;
        foreach ($carts as $cart) {
            Mail::to($cart->email)->send(new AbandonedCartMail($cart));
            $cart->update(['status' => 'emailed']);
            $count++;
        }

        $this->info("Sent {$count} recovery emails.");
    }
}
