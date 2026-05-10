<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Users platform_fee_percentage: " . (Illuminate\Support\Facades\Schema::hasColumn('users', 'platform_fee_percentage') ? 'yes' : 'no') . "\n";
echo "Global setting exists: " . (class_exists('App\Models\Setting') ? 'yes' : 'no') . "\n";
