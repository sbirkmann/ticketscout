<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Illuminate\Support\Facades\DB::table('events')->update(['is_approved' => true]);
Illuminate\Support\Facades\DB::table('locations')->update(['is_approved' => true, 'is_global' => true]);
echo "Approved all events and locations!\n";
