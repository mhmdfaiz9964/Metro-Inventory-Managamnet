<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->string('reason');
            $table->enum('type', [
                'supplier_payment',
                'outsource',
                'company_expenses',
                'others'
            ]);
            $table->text('note')->nullable();
            $table->date('cheque_date');
            $table->string('cheque_bank'); 
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->enum('status', [
                'processing',
                'pending',
                'approved',
                'rejected'
            ])->default('pending');
            $table->string('cheque_no')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};
