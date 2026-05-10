<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_terminals', function (Blueprint $table) {
            $table->string('printer_type')->nullable()->after('is_active'); // 'network', 'bluetooth', 'browser'
            $table->string('printer_ip')->nullable()->after('printer_type');
            $table->string('terminal_type')->default('web')->after('printer_ip'); // 'web', 'stripe_terminal', 'zvt'
        });
    }

    public function down(): void
    {
        Schema::table('pos_terminals', function (Blueprint $table) {
            $table->dropColumn(['printer_type', 'printer_ip', 'terminal_type']);
        });
    }
};
