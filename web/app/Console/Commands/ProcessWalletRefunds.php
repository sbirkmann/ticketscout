<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\WalletTransaction;
use Stripe\StripeClient;
use Carbon\Carbon;

class ProcessWalletRefunds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:refund-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically refund remaining wallet balances 24 hours after event ends.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automated wallet refunds...');

        // Find events that ended more than 24 hours ago and have wallet enabled
        $events = Event::where('enable_wallet', true)
            ->where(function ($q) {
                $q->whereNotNull('end_date')
                  ->where('end_date', '<=', Carbon::now()->subHours(24))
                  ->orWhere(function ($q2) {
                      $q2->whereNull('end_date')
                         ->where('start_date', '<=', Carbon::now()->subHours(36)); // Assume 12h duration if no end date
                  });
            })
            ->get();

        if ($events->isEmpty()) {
            $this->info('No events eligible for wallet refunds at this time.');
            return;
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        foreach ($events as $event) {
            $this->info("Processing event: {$event->title} (ID: {$event->id})");

            $tickets = Ticket::where('event_id', $event->id)
                ->where('wallet_balance', '>', 0)
                ->get();

            foreach ($tickets as $ticket) {
                $amountToRefund = $ticket->wallet_balance;
                
                // Find the latest topup transaction to get the session ID
                $lastTopup = WalletTransaction::where('ticket_id', $ticket->id)
                    ->where('type', 'topup')
                    ->where('description', 'LIKE', '%Session: cs_%')
                    ->latest()
                    ->first();

                if ($lastTopup) {
                    // Extract session ID
                    preg_match('/Session: (cs_[a-zA-Z0-9]+)/', $lastTopup->description, $matches);
                    if (isset($matches[1])) {
                        $sessionId = $matches[1];
                        
                        try {
                            $session = $stripe->checkout->sessions->retrieve($sessionId);
                            if ($session->payment_intent) {
                                // Issue refund
                                $stripe->refunds->create([
                                    'payment_intent' => $session->payment_intent,
                                    'amount' => intval($amountToRefund * 100),
                                ]);

                                // Record transaction
                                WalletTransaction::create([
                                    'ticket_id' => $ticket->id,
                                    'type' => 'refund',
                                    'amount' => $amountToRefund,
                                    'description' => 'Automatische Restguthaben-Auszahlung (Stripe)'
                                ]);

                                $ticket->wallet_balance = 0;
                                $ticket->save();

                                $this->info("Refunded {$amountToRefund} EUR for ticket {$ticket->id}");
                            } else {
                                $this->error("No PaymentIntent found for session {$sessionId} on ticket {$ticket->id}");
                            }
                        } catch (\Exception $e) {
                            $this->error("Stripe refund failed for ticket {$ticket->id}: " . $e->getMessage());
                        }
                    } else {
                        $this->error("Could not parse session ID for ticket {$ticket->id}");
                    }
                } else {
                    $this->error("No topup transaction found to refund against for ticket {$ticket->id}");
                }
            }

            // Optional: Mark event as 'wallet_refunded' = true so we don't process it again
            // We don't have this column yet, but we could add it, or we rely on wallet_balance > 0 check which we do.
        }

        $this->info('Wallet refunds completed.');
    }
}
