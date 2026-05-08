<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->integer('quantity')->nullable()->after('price');
            $table->integer('sold')->default(0)->after('quantity');
            $table->boolean('is_default')->default(false)->after('sold');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'sold', 'is_default']);
        });
    }
};
