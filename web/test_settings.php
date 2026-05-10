<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$global = Illuminate\Support\Facades\Schema::getColumnListing('global_settings');
$vendor = Illuminate\Support\Facades\Schema::getColumnListing('vendor_settings');

echo "global_settings: " . implode(', ', $global) . "\n";
echo "vendor_settings: " . implode(', ', $vendor) . "\n";
