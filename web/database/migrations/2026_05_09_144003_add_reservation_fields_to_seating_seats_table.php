<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seating_seats', function (Blueprint $table) {
            $table->string('reservation_token')->nullable()->after('status');
            $table->timestamp('reserved_until')->nullable()->after('reservation_token');
        });
    }

    public function down(): void
    {
        Schema::table('seating_seats', function (Blueprint $table) {
            $table->dropColumn(['reservation_token', 'reserved_until']);
        });
    }
};
