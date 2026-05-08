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
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->string('invoice_prefix')->nullable()->default('RE-');
            $table->integer('invoice_next_number')->default(1);
            $table->string('invoice_logo_path')->nullable();
            $table->text('invoice_footer_text')->nullable();
            $table->text('invoice_tax_info')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_prefix',
                'invoice_next_number',
                'invoice_logo_path',
                'invoice_footer_text',
                'invoice_tax_info',
            ]);
        });
    }
};
