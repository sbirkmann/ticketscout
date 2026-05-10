<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('pos_article_categories')->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->decimal('default_price', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(19.00); // 19%, 7%, 0%
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_articles');
    }
};
