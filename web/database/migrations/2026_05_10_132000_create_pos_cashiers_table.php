<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_cashiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('pin', 4); // 4-digit PIN
            $table->timestamps();
        });

        // Add cashier to receipt
        Schema::table('pos_receipts', function (Blueprint $table) {
            $table->foreignId('pos_cashier_id')->nullable()->constrained('pos_cashiers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pos_receipts', function (Blueprint $table) {
            $table->dropForeign(['pos_cashier_id']);
            $table->dropColumn('pos_cashier_id');
        });
        Schema::dropIfExists('pos_cashiers');
    }
};
