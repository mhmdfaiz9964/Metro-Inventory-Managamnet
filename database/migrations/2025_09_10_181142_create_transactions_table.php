<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('cheque_id')->nullable();
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('from_bank_id');
            $table->string('to_bank_id')->nullable();
            $table->enum('type', ['credited', 'debited']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
