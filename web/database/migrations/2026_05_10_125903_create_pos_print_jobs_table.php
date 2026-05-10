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
        Schema::create('pos_print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_terminal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pos_receipt_id')->nullable()->constrained()->nullOnDelete();
            $table->longText('html_content'); // HTML to print
            $table->string('status')->default('pending'); // pending, printed, failed
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_print_jobs');
    }
};
