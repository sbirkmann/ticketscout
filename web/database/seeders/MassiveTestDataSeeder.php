<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Location;
use App\Models\Event;
use App\Models\TicketCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MassiveTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendor = User::role('vendor')->first();
        if (!$vendor) {
            $this->command->error("No vendor found. Please run the initial seeder first.");
            return;
        }

        $this->command->info("Generating massive test data for Vendor: {$vendor->name}...");

        // 1. Generate Locations
        $locations = [];
        for ($i = 1; $i <= 5; $i++) {
            $locations[] = Location::create([
                'vendor_id' => $vendor->id,
                'name' => "Großraum-Halle $i",
                'address' => "Musterstraße $i",
                'zip' => "1011$i",
                'city' => 'Berlin',
                'country' => 'DE',
                'is_global' => false,
            ]);
        }

        // 2. Generate Events
        $eventCategories = \App\Models\EventCategory::all();
        $events = [];
        
        foreach ($locations as $location) {
            for ($j = 1; $j <= 10; $j++) { // 10 events per location = 50 events
                $date = Carbon::now()->addDays(rand(-30, 90)); // some in past, some in future
                $events[] = Event::create([
                    'vendor_id' => $vendor->id,
                    'location_id' => $location->id,
                    'event_category_id' => $eventCategories->random()->id,
                    'title' => "Mega Event " . Str::random(5) . " " . $j,
                    'slug' => Str::slug("Mega Event " . Str::random(5) . " " . $j),
                    'description' => "Dies ist eine Testbeschreibung für ein massiv generiertes Event.",
                    'start_date' => $date,
                    'end_date' => (clone $date)->addHours(4),
                    'status' => $date->isPast() ? 'past' : 'published',
                ]);
            }
        }

        // 3. Generate Ticket Categories for Events
        $allCategories = [];
        foreach ($events as $event) {
            $cat1 = TicketCategory::create([
                'event_id' => $event->id,
                'name' => 'Standard Ticket',
                'price' => rand(20, 50),
                'capacity' => rand(100, 500)
            ]);
            $cat2 = TicketCategory::create([
                'event_id' => $event->id,
                'name' => 'VIP Ticket',
                'price' => rand(80, 150),
                'capacity' => rand(20, 50)
            ]);
            $allCategories[] = $cat1;
            $allCategories[] = $cat2;
        }

        // 4. Generate Orders & Tickets
        $this->command->info("Generating orders and tickets...");
        $users = User::role('customer')->get();
        if ($users->isEmpty()) {
            $customer = User::create([
                'name' => 'Test Customer',
                'email' => 'customer_test_' . rand(1,100) . '@test.com',
                'password' => bcrypt('password')
            ]);
            $customer->assignRole('customer');
            $users->push($customer);
        }

        for ($k = 0; $k < 100; $k++) { // 100 orders
            $event = $events[array_rand($events)];
            $category = $event->ticketCategories->random();
            $qty = rand(1, 4);
            $orderDate = (clone $event->start_date)->subDays(rand(1, 30));

            $net = ($category->price * $qty) / 1.19;
            $tax = ($category->price * $qty) - $net;
            $gross = $category->price * $qty;
            
            $platformFee = $gross * 0.05 + ($qty * 1.0); // 5% + 1€ pro Ticket

            $order = Order::create([
                'user_id' => $users->random()->id,
                'event_id' => $event->id,
                'status' => 'completed',
                'billing_first_name' => 'Max',
                'billing_last_name' => 'Muster ' . $k,
                'billing_street' => 'Teststr. 1',
                'billing_zip' => '12345',
                'billing_city' => 'Teststadt',
                'billing_country' => 'DE',
                'tax_rate' => 19,
                'tax_amount' => ($gross - ($gross / 1.19)),
                'total_amount' => $gross,
                'platform_fee' => $platformFee,
                'stripe_payment_intent_id' => 'pi_' . Str::random(24),
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'ticket_category_id' => $category->id,
                'quantity' => $qty,
                'price' => $category->price,
            ]);

            for ($t = 0; $t < $qty; $t++) {
                Ticket::create([
                    'order_id' => $order->id,
                    'ticket_category_id' => $category->id,
                    'qr_code_hash' => hash('sha256', Str::random(40)),
                    'status' => 'valid',
                ]);
            }

            // Generate vendor to customer invoice
            Invoice::create([
                'vendor_id' => $vendor->id,
                'order_id' => $order->id,
                'type' => 'vendor_to_customer',
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'net' => $net,
                'tax' => $tax,
                'gross' => $gross,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);
        }

        $this->command->info("Data generation complete.");
    }
}
