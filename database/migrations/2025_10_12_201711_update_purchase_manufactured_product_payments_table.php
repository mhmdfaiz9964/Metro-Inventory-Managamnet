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
        Schema::table('purchase_manufactured_product_payments', function (Blueprint $table) {
            // Drop the old columns
            if (Schema::hasColumn('purchase_manufactured_product_payments', 'fund_transfer_from_bank_id')) {
                $table->dropColumn('fund_transfer_from_bank_id');
            }

            if (Schema::hasColumn('purchase_manufactured_product_payments', 'fund_transfer_to_bank_id')) {
                $table->dropColumn('fund_transfer_to_bank_id');
            }

            // Add new columns
            $table->string('bank_id')->nullable()->after('payment_method');
            $table->string('bank_account_id')->nullable()->after('bank_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_manufactured_product_payments', function (Blueprint $table) {
            // Remove new columns
            if (Schema::hasColumn('purchase_manufactured_product_payments', 'bank_id')) {
                $table->dropColumn('bank_id');
            }

            if (Schema::hasColumn('purchase_manufactured_product_payments', 'bank_account_id')) {
                $table->dropColumn('bank_account_id');
            }

            // Re-add old columns
            $table->unsignedBigInteger('fund_transfer_from_bank_id')->nullable()->after('payment_method');
            $table->unsignedBigInteger('fund_transfer_to_bank_id')->nullable()->after('fund_transfer_from_bank_id');
        });
    }
};
