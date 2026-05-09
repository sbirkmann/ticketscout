<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EventCategory;
use App\Models\Location;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure a vendor exists
        $vendor = User::firstOrCreate(
            ['email' => 'vendor@ticketsout24.de'],
            [
                'name' => 'Demo Veranstalter',
                'password' => bcrypt('password'),
            ]
        );
        if (!$vendor->hasRole('vendor')) {
            $vendor->assignRole('vendor');
        }

        // 2. Categories
        $categories = [
            [
                'name' => 'Konzerte & Tourneen',
                'slug' => 'konzerte',
                'description' => 'Die besten Live-Bands und Künstler.',
                'image_path' => 'events/demo_concert.png',
            ],
            [
                'name' => 'Comedy & Show',
                'slug' => 'comedy',
                'description' => 'Lachen bis der Arzt kommt.',
                'image_path' => 'events/demo_comedy.png',
            ],
            [
                'name' => 'Festivals',
                'slug' => 'festivals',
                'description' => 'Open-Air, Sommer und gute Vibes.',
                'image_path' => 'events/demo_festival.png',
            ]
        ];

        $createdCategories = [];
        foreach ($categories as $cat) {
            $createdCategories[$cat['slug']] = EventCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 3. Locations
        $stadium = Location::updateOrCreate(['slug' => 'olympiastadion-berlin'], [
            'name' => 'Olympiastadion Berlin',
            'slug' => 'olympiastadion-berlin',
            'city' => 'Berlin',
            'zip' => '14053',
            'address' => 'Olympischer Platz 3',
            'country' => 'Deutschland',
            'description' => 'Die legendäre Arena für die größten Shows.',
            'banner_image_path' => 'locations/demo_stadium.png',
            'is_global' => true,
        ]);

        $club = Location::updateOrCreate(['slug' => 'astra-kulturhaus'], [
            'name' => 'Astra Kulturhaus',
            'slug' => 'astra-kulturhaus',
            'city' => 'Berlin',
            'zip' => '10245',
            'address' => 'Revaler Str. 99',
            'country' => 'Deutschland',
            'description' => 'Legendärer Club im Herzen von Friedrichshain.',
            'is_global' => true,
        ]);

        // 4. Events
        $rockEvent = Event::updateOrCreate(['slug' => 'rock-legends-tour-2026'], [
            'title' => 'Rock Legends Tour 2026',
            'slug' => 'rock-legends-tour-2026',
            'description' => "Die größten Rock-Hymnen aller Zeiten live im Olympiastadion Berlin.\n\nErlebe unvergessliche Momente mit den Legenden des Rock – von Classic Rock bis Modern Metal. Mit beeindruckenden Lichtshows, Pyrotechnik und einer Setlist, die die Geschichte des Rocks erzählt.\n\n🎸 Bühnenöffnung: 18:00 Uhr\n🎤 Konzertbeginn: 20:00 Uhr",
            'start_date' => Carbon::now()->addDays(14)->setHour(20)->setMinute(0),
            'end_date' => Carbon::now()->addDays(14)->setHour(23)->setMinute(0),
            'location_id' => $stadium->id,
            'event_category_id' => $createdCategories['konzerte']->id,
            'vendor_id' => $vendor->id,
            'image_path' => 'events/demo_concert.png',
            'status' => 'published',
            'tags' => ['Rock', 'Stadion', 'Live'],
        ]);

        // Ticket categories for Rock Event
        \App\Models\TicketCategory::updateOrCreate(
            ['event_id' => $rockEvent->id, 'name' => 'Standard'],
            ['event_id' => $rockEvent->id, 'name' => 'Standard', 'price' => 49.00, 'quantity' => 5000, 'sold' => 0, 'is_default' => true]
        );
        \App\Models\TicketCategory::updateOrCreate(
            ['event_id' => $rockEvent->id, 'name' => 'VIP'],
            ['event_id' => $rockEvent->id, 'name' => 'VIP', 'price' => 129.00, 'quantity' => 500, 'sold' => 0]
        );
        \App\Models\TicketCategory::updateOrCreate(
            ['event_id' => $rockEvent->id, 'name' => 'Golden Circle'],
            ['event_id' => $rockEvent->id, 'name' => 'Golden Circle', 'price' => 89.00, 'quantity' => 1000, 'sold' => 0]
        );

        $comedyEvent = Event::updateOrCreate(['slug' => 'comedy-night-special'], [
            'title' => 'Comedy Night Special',
            'slug' => 'comedy-night-special',
            'description' => "Der lustigste Abend des Jahres! Deutschlands beste Stand-Up-Comedians auf einer Bühne.\n\nEine unvergessliche Nacht voller Lachen, Pointen und Überraschungsgäste.\n\n🎤 Einlass: 19:30 Uhr\n😂 Showbeginn: 20:30 Uhr",
            'start_date' => Carbon::now()->addDays(5)->setHour(20)->setMinute(30),
            'end_date' => Carbon::now()->addDays(5)->setHour(22)->setMinute(30),
            'location_id' => $club->id,
            'event_category_id' => $createdCategories['comedy']->id,
            'vendor_id' => $vendor->id,
            'image_path' => 'events/demo_comedy.png',
            'status' => 'published',
            'tags' => ['Comedy', 'Stand-up', 'Lachen'],
        ]);

        \App\Models\TicketCategory::updateOrCreate(
            ['event_id' => $comedyEvent->id, 'name' => 'Standard'],
            ['event_id' => $comedyEvent->id, 'name' => 'Standard', 'price' => 24.90, 'quantity' => 800, 'sold' => 0, 'is_default' => true]
        );
        \App\Models\TicketCategory::updateOrCreate(
            ['event_id' => $comedyEvent->id, 'name' => 'VIP (inkl. Meet & Greet)'],
            ['event_id' => $comedyEvent->id, 'name' => 'VIP (inkl. Meet & Greet)', 'price' => 59.90, 'quantity' => 50, 'sold' => 0]
        );

        $festivalEvent = Event::updateOrCreate(['slug' => 'summer-vibes-festival-2026'], [
            'title' => 'Summer Vibes Festival 2026',
            'slug' => 'summer-vibes-festival-2026',
            'description' => "3 Tage Musik, Sonne und unvergessliche Momente unter freiem Himmel.\n\nMit über 40 Künstlern auf 5 Bühnen, Food-Village, Camping und einem Erlebnis für die Ewigkeit.\n\n⛺ Camping ab: Freitag 12:00 Uhr\n🎶 Programm: Freitag – Sonntag",
            'start_date' => Carbon::now()->addMonths(2)->setHour(14)->setMinute(0),
            'end_date' => Carbon::now()->addMonths(2)->addDays(2)->setHour(23)->setMinute(0),
            'location_id' => $stadium->id,
            'event_category_id' => $createdCategories['festivals']->id,
            'vendor_id' => $vendor->id,
            'image_path' => 'events/demo_festival.png',
            'status' => 'published',
            'tags' => ['Festival', 'Outdoor', 'EDM', 'Pop'],
        ]);

        \App\Models\TicketCategory::updateOrCreate(
            ['event_id' => $festivalEvent->id, 'name' => 'Tagesticket'],
            ['event_id' => $festivalEvent->id, 'name' => 'Tagesticket', 'price' => 69.00, 'quantity' => 10000, 'sold' => 0, 'is_default' => true]
        );
        \App\Models\TicketCategory::updateOrCreate(
            ['event_id' => $festivalEvent->id, 'name' => '3-Tage-Ticket'],
            ['event_id' => $festivalEvent->id, 'name' => '3-Tage-Ticket', 'price' => 159.00, 'quantity' => 5000, 'sold' => 0]
        );
        \App\Models\TicketCategory::updateOrCreate(
            ['event_id' => $festivalEvent->id, 'name' => 'Camping + 3-Tage-Ticket'],
            ['event_id' => $festivalEvent->id, 'name' => 'Camping + 3-Tage-Ticket', 'price' => 199.00, 'quantity' => 2000, 'sold' => 0]
        );

        // 5. Artists
        $artist1 = \App\Models\Artist::updateOrCreate(['slug' => 'die-rocker'], [
            'name' => 'Die Rocker',
            'slug' => 'die-rocker',
            'bio' => 'Deutschlands beste Rockband.',
        ]);
        $rockEvent->artists()->syncWithoutDetaching([$artist1->id]);

        $artist2 = \App\Models\Artist::updateOrCreate(['slug' => 'max-lacher'], [
            'name' => 'Max Lacher',
            'slug' => 'max-lacher',
            'bio' => 'Der Stand-up Star.',
        ]);
        $comedyEvent->artists()->syncWithoutDetaching([$artist2->id]);

        $artist3 = \App\Models\Artist::updateOrCreate(['slug' => 'the-indies'], [
            'name' => 'The Indies',
            'slug' => 'the-indies',
            'bio' => 'Die aufstrebende Indie-Band aus Berlin.',
        ]);
        $artist4 = \App\Models\Artist::updateOrCreate(['slug' => 'dj-electro'], [
            'name' => 'DJ Electro',
            'slug' => 'dj-electro',
            'bio' => 'House und Techno Beats.',
        ]);

        $stadium->update(['city' => 'Berlin']);
        $club->update(['city' => 'Berlin']);

        $hamburgArena = Location::updateOrCreate(['slug' => 'hamburg-arena'], [
            'name' => 'Hamburg Arena',
            'slug' => 'hamburg-arena',
            'city' => 'Hamburg',
            'zip' => '22525',
            'address' => 'Sylvesterallee 10',
            'country' => 'Deutschland',
            'description' => 'Die größte Mehrzweckarena Norddeutschlands.',
            'banner_image_path' => 'locations/demo_stadium.png',
            'is_global' => true,
        ]);

        $munichHall = Location::updateOrCreate(['slug' => 'munich-olympiapark'], [
            'name' => 'Olympiapark München',
            'slug' => 'munich-olympiapark',
            'city' => 'München',
            'zip' => '80809',
            'address' => 'Spiridon-Louis-Ring 21',
            'country' => 'Deutschland',
            'description' => 'Traditionsreicher Park für Großevents.',
            'is_global' => true,
        ]);

        // More Events
        $indieEvent = Event::updateOrCreate(['slug' => 'indie-night-hamburg'], [
            'title' => 'Indie Night Hamburg',
            'slug' => 'indie-night-hamburg',
            'description' => "Die beste Indie-Musik live in der Hamburg Arena.",
            'start_date' => Carbon::now()->addDays(20)->setHour(20)->setMinute(0),
            'end_date' => Carbon::now()->addDays(20)->setHour(23)->setMinute(0),
            'location_id' => $hamburgArena->id,
            'event_category_id' => $createdCategories['konzerte']->id,
            'vendor_id' => $vendor->id,
            'image_path' => 'events/demo_concert.png',
            'status' => 'published',
            'tags' => ['Indie', 'Live'],
        ]);
        $indieEvent->artists()->syncWithoutDetaching([$artist3->id]);
        
        $catIndie1 = \App\Models\TicketCategory::updateOrCreate(
            ['event_id' => $indieEvent->id, 'name' => 'Stehplatz'],
            ['event_id' => $indieEvent->id, 'name' => 'Stehplatz', 'price' => 35.00, 'quantity' => 2000, 'sold' => 0, 'is_default' => true]
        );

        $electroEvent = Event::updateOrCreate(['slug' => 'electro-beats-munich'], [
            'title' => 'Electro Beats München',
            'slug' => 'electro-beats-munich',
            'description' => "Die größte Techno-Party des Jahres.",
            'start_date' => Carbon::now()->addDays(30)->setHour(22)->setMinute(0),
            'end_date' => Carbon::now()->addDays(31)->setHour(6)->setMinute(0),
            'location_id' => $munichHall->id,
            'event_category_id' => $createdCategories['festivals']->id,
            'vendor_id' => $vendor->id,
            'image_path' => 'events/demo_festival.png',
            'status' => 'published',
            'tags' => ['Electro', 'Party', 'Techno'],
        ]);
        $electroEvent->artists()->syncWithoutDetaching([$artist4->id]);
        
        $catElectro1 = \App\Models\TicketCategory::updateOrCreate(
            ['event_id' => $electroEvent->id, 'name' => 'Early Bird'],
            ['event_id' => $electroEvent->id, 'name' => 'Early Bird', 'price' => 20.00, 'quantity' => 500, 'sold' => 0, 'is_default' => true]
        );
        $catElectro2 = \App\Models\TicketCategory::updateOrCreate(
            ['event_id' => $electroEvent->id, 'name' => 'Regular Ticket'],
            ['event_id' => $electroEvent->id, 'name' => 'Regular Ticket', 'price' => 30.00, 'quantity' => 2000, 'sold' => 0]
        );

        // Addons for Rock Event
        $addonVIP = \App\Models\Addon::updateOrCreate(
            ['event_id' => $rockEvent->id, 'name' => 'VIP Upgrade'],
            ['price' => 50.00, 'quantity' => 100]
        );
        $addonParking = \App\Models\Addon::updateOrCreate(
            ['event_id' => $rockEvent->id, 'name' => 'Parkplatz Ticket'],
            ['price' => 15.00, 'quantity' => 500]
        );

        // Map AddonVIP only to specific Ticket Categories (e.g., Golden Circle)
        $goldenCircleCat = \App\Models\TicketCategory::where('event_id', $rockEvent->id)->where('name', 'Golden Circle')->first();
        if ($goldenCircleCat) {
            $addonVIP->ticketCategories()->sync([$goldenCircleCat->id]);
        }

        echo "Demo Daten erfolgreich erstellt!\n";
    }
}
