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
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('managing_director')->nullable();
            $table->string('commercial_register')->nullable();
            $table->string('vat_id')->nullable();
            $table->string('iban')->nullable();
            $table->string('bic')->nullable();
            $table->string('bank_name')->nullable();
            $table->boolean('e_invoice_enabled')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->dropColumn([
                'company_name', 'company_address', 'tax_number', 'managing_director',
                'commercial_register', 'vat_id', 'iban', 'bic', 'bank_name', 'e_invoice_enabled'
            ]);
        });
    }
};
