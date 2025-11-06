<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_payments', function (Blueprint $table) {
            $table->foreignId('bank_account_id')
                  ->nullable()
                  ->after('paid_by')
                  ->constrained('bank_accounts')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales_payments', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn('bank_account_id');
        });
    }
};
