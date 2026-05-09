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
        Schema::create('group_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('share_token')->unique(); // unique link token
            $table->integer('total_tickets'); // how many total tickets in the group
            $table->decimal('price_per_ticket', 8, 2);
            $table->string('ticket_category');
            $table->string('status')->default('open'); // open, complete, expired
            $table->json('participants')->nullable(); // [{email, paid_at, order_id}]
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_reservations');
    }
};
