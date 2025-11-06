<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receiving_cheques', function (Blueprint $table) {
            $table->enum('cheque_type', ['Crossed Cheque', 'Cash Cheque'])
                  ->default('Crossed Cheque')
                  ->after('cheque_no');

            $table->unsignedBigInteger('paid_bank_account_id')->nullable()->after('status');

            $table->foreign('paid_bank_account_id')->references('id')->on('bank_accounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('receiving_cheques', function (Blueprint $table) {
            $table->dropForeign(['paid_bank_account_id']);
            $table->dropColumn(['cheque_type', 'paid_bank_account_id']);
        });
    }
};
