<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            // Steuersatz in Prozent, z.B. 19.00 oder 7.00
            $table->decimal('tax_rate', 5, 2)->default(19.00)->after('sender_name');
            // Ob Preise bereits inkl. MwSt. (brutto) oder netto angegeben werden
            $table->boolean('prices_include_tax')->default(true)->after('tax_rate');
            // Steuerbefreiung möglich (z.B. Kleinunternehmer nach §19 UStG)
            $table->boolean('tax_exempt')->default(false)->after('prices_include_tax');
            $table->string('tax_id')->nullable()->after('tax_exempt'); // USt-IdNr.
        });
    }

    public function down(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->dropColumn(['tax_rate', 'prices_include_tax', 'tax_exempt', 'tax_id']);
        });
    }
};
