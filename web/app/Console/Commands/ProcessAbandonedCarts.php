<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\AbandonedCart;
use App\Mail\AbandonedCartMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

#[Signature('ticket:process-abandoned-carts')]
#[Description('Sends recovery emails to users with abandoned carts older than 1 hour')]
class ProcessAbandonedCarts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $carts = AbandonedCart::where('status', 'pending')
            ->where('updated_at', '<=', Carbon::now()->subHour())
            ->with('event')
            ->get();

        $count = 0;
        foreach ($carts as $cart) {
            if ($cart->email && $cart->event) {
                Mail::to($cart->email)->send(new AbandonedCartMail($cart));
                $cart->update(['status' => 'sent']);
                $count++;
            }
        }

        $this->info("Sent {$count} abandoned cart emails.");
    }
}
