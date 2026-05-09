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
        Schema::create('seating_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seating_plan_id')->constrained()->onDelete('cascade');
            $table->string('label'); // e.g. "Reihe A", "Row 1"
            $table->integer('row_number')->default(1);
            $table->integer('seat_count')->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seating_rows');
    }
};
