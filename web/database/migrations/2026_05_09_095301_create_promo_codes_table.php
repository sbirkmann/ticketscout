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
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained()->onDelete('cascade'); // optional
            $table->string('code');
            $table->enum('type', ['percent', 'fixed']);
            $table->decimal('value', 8, 2);
            $table->integer('max_uses')->nullable();
            $table->integer('current_uses')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->unique(['vendor_id', 'code']); // code must be unique per vendor
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
