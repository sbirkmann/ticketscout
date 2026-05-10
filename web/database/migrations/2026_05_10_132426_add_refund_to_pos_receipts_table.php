<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_receipts', function (Blueprint $table) {
            $table->foreignId('refund_for_receipt_id')->nullable()->constrained('pos_receipts')->nullOnDelete()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('pos_receipts', function (Blueprint $table) {
            $table->dropForeign(['refund_for_receipt_id']);
            $table->dropColumn('refund_for_receipt_id');
        });
    }
};
