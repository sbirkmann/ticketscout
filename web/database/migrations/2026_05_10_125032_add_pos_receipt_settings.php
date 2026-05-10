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
            $table->text('pos_receipt_header')->nullable()->after('has_advanced_pos');
            $table->text('pos_receipt_footer')->nullable()->after('pos_receipt_header');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->text('pos_receipt_header_override')->nullable()->after('enable_wallet');
            $table->text('pos_receipt_footer_override')->nullable()->after('pos_receipt_header_override');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->dropColumn(['pos_receipt_header', 'pos_receipt_footer']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['pos_receipt_header_override', 'pos_receipt_footer_override']);
        });
    }
};
