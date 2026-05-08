<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('billing_first_name')->nullable()->after('guest_email');
            $table->string('billing_last_name')->nullable()->after('billing_first_name');
            $table->string('billing_company')->nullable()->after('billing_last_name');
            $table->string('billing_street')->nullable()->after('billing_company');
            $table->string('billing_zip')->nullable()->after('billing_street');
            $table->string('billing_city')->nullable()->after('billing_zip');
            $table->string('billing_country')->default('DE')->after('billing_city');
            $table->string('billing_phone')->nullable()->after('billing_country');
            $table->decimal('tax_rate', 5, 2)->default(19.00)->after('platform_fee');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_rate');
            $table->boolean('agb_accepted')->default(false)->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'billing_first_name','billing_last_name','billing_company',
                'billing_street','billing_zip','billing_city','billing_country',
                'billing_phone','tax_rate','tax_amount','agb_accepted'
            ]);
        });
    }
};
