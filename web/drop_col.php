<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('events', function (Blueprint $table) {
    if (Schema::hasColumn('events', 'event_category_id')) {
        $table->dropColumn('event_category_id');
    }
});
echo "Dropped event_category_id\n";
