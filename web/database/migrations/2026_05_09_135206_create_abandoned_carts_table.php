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
        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, recovered, sent
            $table->json('cart_data')->nullable(); // Optional: Speichert Ticketanzahl/Kategorien
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abandoned_carts');
    }
};
