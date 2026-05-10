<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pos_article_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('tax_rate', 5, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_receipt_items');
    }
};
