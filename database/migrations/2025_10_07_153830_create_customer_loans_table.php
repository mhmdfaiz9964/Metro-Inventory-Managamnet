<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_loans', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->string('customer_name');
            $table->date('date');
            $table->date('loan_due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->text('reason')->nullable();
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('from_bank_account_id')->nullable();
            $table->timestamps();

            $table->foreign('from_bank_account_id')
                  ->references('id')
                  ->on('bank_accounts')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_loans');
    }
};
