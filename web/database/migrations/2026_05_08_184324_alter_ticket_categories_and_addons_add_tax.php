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
            $table->decimal('tax_rate', 5, 2)->default(0)->after('price');
        });

        Schema::table('addons', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(0)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->dropColumn('tax_rate');
        });

        Schema::table('addons', function (Blueprint $table) {
            $table->dropColumn('tax_rate');
        });
    }
};
