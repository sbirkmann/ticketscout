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
            $table->boolean('enable_wallet')->default(false)->after('status');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->decimal('wallet_balance', 8, 2)->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('enable_wallet');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('wallet_balance');
        });
    }
};
