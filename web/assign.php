<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$vendor = App\Models\User::role('vendor')->first();
if ($vendor) {
    Illuminate\Support\Facades\DB::table('events')->update(['vendor_id' => $vendor->id]);
    echo "Assigned all events to vendor ID: " . $vendor->id . "\n";
} else {
    echo "No vendor found!\n";
}
