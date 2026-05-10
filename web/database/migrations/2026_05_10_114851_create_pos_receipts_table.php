<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pos_terminal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pos_shift_id')->nullable()->constrained()->nullOnDelete();
            
            $table->bigInteger('receipt_number'); // Sequential per vendor
            
            $table->decimal('total_gross', 10, 2);
            $table->decimal('total_net', 10, 2);
            $table->json('tax_details')->nullable(); // Store tax breakdown
            
            $table->string('payment_method')->default('cash'); // cash, wallet, card
            $table->string('payment_reference')->nullable(); // Stripe ID, Ticket ID, etc.
            
            $table->string('status')->default('paid'); // paid, voided (storniert)
            $table->timestamps();
            
            $table->unique(['vendor_id', 'receipt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_receipts');
    }
};
