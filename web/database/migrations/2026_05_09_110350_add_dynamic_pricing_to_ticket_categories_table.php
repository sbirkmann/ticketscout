<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->boolean('use_dynamic_pricing')->default(false)->after('capacity');
            $table->integer('dynamic_pricing_threshold_percent')->nullable()->after('use_dynamic_pricing')->comment('Trigger price increase when only X% tickets remain');
            $table->decimal('dynamic_pricing_increase_amount', 8, 2)->nullable()->after('dynamic_pricing_threshold_percent')->comment('Increase price by this amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->dropColumn([
                'use_dynamic_pricing',
                'dynamic_pricing_threshold_percent',
                'dynamic_pricing_increase_amount'
            ]);
        });
    }
};
