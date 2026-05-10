<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_terminal_id')->constrained('pos_terminals')->cascadeOnDelete();
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete(); // Optionally closed by same or someone else
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('starting_cash', 10, 2)->default(0); // Wechselgeld Bestand am Anfang
            $table->decimal('ending_cash', 10, 2)->nullable(); // Tatsächlich gezähltes Geld am Ende
            $table->decimal('expected_cash', 10, 2)->nullable(); // System-Berechnetes Geld am Ende
            $table->decimal('difference', 10, 2)->nullable(); // Ist minus Soll
            $table->string('status')->default('open'); // open, closed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_shifts');
    }
};
