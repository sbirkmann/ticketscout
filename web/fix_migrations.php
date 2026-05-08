<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Fix artist_event migration record - it ran partially
DB::table('migrations')->where('migration', '2026_05_08_195045_create_artist_event_table')->delete();
echo "artist_event migration record removed.\n";

// Fix ticket_categories migration record if already ran
DB::table('migrations')->where('migration', '2026_05_08_195128_alter_ticket_categories_add_quantity_sold_default')->delete();
echo "ticket_categories migration record removed.\n";

// Add missing columns to ticket_categories if not present
if (!Schema::hasColumn('ticket_categories', 'quantity')) {
    Schema::table('ticket_categories', function($table) {
        $table->integer('quantity')->nullable()->after('price');
        $table->integer('sold')->default(0)->after('quantity');
        $table->boolean('is_default')->default(false)->after('sold');
    });
    echo "ticket_categories columns added.\n";
} else {
    echo "ticket_categories columns already exist.\n";
}

// Add foreign keys to artist_event if missing
if (!Schema::hasColumn('artist_event', 'artist_id')) {
    Schema::table('artist_event', function($table) {
        $table->foreignId('artist_id')->constrained()->onDelete('cascade');
        $table->foreignId('event_id')->constrained()->onDelete('cascade');
        $table->string('role')->nullable();
    });
    echo "artist_event columns added.\n";
} else {
    echo "artist_event columns already exist.\n";
}

// Mark migrations as run
DB::table('migrations')->insert(['migration' => '2026_05_08_195045_create_artist_event_table', 'batch' => 10]);
DB::table('migrations')->insert(['migration' => '2026_05_08_195128_alter_ticket_categories_add_quantity_sold_default', 'batch' => 10]);
echo "Done!\n";
