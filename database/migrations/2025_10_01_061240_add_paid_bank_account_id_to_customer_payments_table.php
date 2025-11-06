<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->foreignId('paid_bank_account_id')
                ->nullable()
                ->constrained('bank_accounts')
                ->onDelete('set null')
                ->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropForeign(['paid_bank_account_id']);
            $table->dropColumn('paid_bank_account_id');
        });
    }
};
