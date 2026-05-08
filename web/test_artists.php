<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$artists = \App\Models\Artist::all();
echo "Artists Count: " . $artists->count() . "\n";
foreach($artists as $a) {
    echo $a->name . " | " . $a->slug . " | pub: " . $a->is_published . "\n";
}
