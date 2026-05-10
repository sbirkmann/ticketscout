<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_shift_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['deposit', 'withdrawal']); // Einlage, Abschöpfung
            $table->decimal('amount', 10, 2);
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_cash_transactions');
    }
};
