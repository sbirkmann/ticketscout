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
        Schema::create('seating_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seating_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('seating_row_id')->nullable()->constrained('seating_rows')->onDelete('cascade');
            $table->string('label'); // e.g. "A12", "Reihe B Platz 5"
            $table->string('category')->default('standard'); // maps to ticket category name
            $table->decimal('price_override', 8, 2)->nullable(); // if null, use ticket cat price
            $table->integer('row_number')->default(1);
            $table->integer('seat_number')->default(1);
            $table->string('status')->default('available'); // available, reserved, sold
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seating_seats');
    }
};
