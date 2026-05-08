<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$locations = App\Models\Location::all();
foreach($locations as $loc) {
    if (empty($loc->slug)) {
        $loc->slug = \Illuminate\Support\Str::slug($loc->name) . '-' . uniqid();
        $loc->save();
    }
}
echo "Location slugs generated!\n";
