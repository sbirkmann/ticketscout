<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addons', function (Blueprint $table) {
            // Fixed max quantity (e.g. max 3 total per order)
            $table->unsignedInteger('max_qty')->nullable()->after('price');
            // Per-ticket multiplier (e.g. max 1 per ticket in cart)
            $table->boolean('max_per_ticket')->default(false)->after('max_qty');
        });
    }

    public function down(): void
    {
        Schema::table('addons', function (Blueprint $table) {
            $table->dropColumn(['max_qty', 'max_per_ticket']);
        });
    }
};
