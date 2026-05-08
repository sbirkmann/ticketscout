<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_settings', 'stripe_account_id')) {
                $table->string('stripe_account_id')->nullable();
            }
            if (!Schema::hasColumn('vendor_settings', 'stripe_onboarding_completed')) {
                $table->boolean('stripe_onboarding_completed')->default(false);
            }
            if (!Schema::hasColumn('vendor_settings', 'invoice_prefix')) {
                $table->string('invoice_prefix')->default('RE-');
            }
            if (!Schema::hasColumn('vendor_settings', 'invoice_footer_text')) {
                $table->text('invoice_footer_text')->nullable();
            }
            if (!Schema::hasColumn('vendor_settings', 'disable_invoicing')) {
                $table->boolean('disable_invoicing')->default(false);
            }
            if (!Schema::hasColumn('vendor_settings', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(19.00);
            }
        });
    }

    public function down(): void
    {
        // No down needed for safety
    }
};
