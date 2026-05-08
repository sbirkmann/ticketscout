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
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('seating_plan_id')->nullable()->constrained('seating_plans')->nullOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('seat_info')->nullable()->after('quantity');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('seat_info')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['seating_plan_id']);
            $table->dropColumn('seating_plan_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('seat_info');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('seat_info');
        });
    }
};
