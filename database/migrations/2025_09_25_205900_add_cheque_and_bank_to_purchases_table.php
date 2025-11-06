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
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('bank_account_id')->nullable()->after('payment_method');
            $table->string('cheque_no')->nullable()->after('bank_account_id');
            $table->date('cheque_date')->nullable()->after('cheque_no');
            $table->string('paid_to')->nullable()->after('cheque_date');

            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn(['bank_account_id', 'cheque_no', 'cheque_date', 'paid_to']);
        });
    }
};
