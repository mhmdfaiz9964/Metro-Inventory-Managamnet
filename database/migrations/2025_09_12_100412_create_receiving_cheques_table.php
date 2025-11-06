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
        Schema::create('receiving_cheques', function (Blueprint $table) {
            $table->id();
            $table->string('cheque_no')->unique(); 
            $table->string('bank'); 
            $table->string('paid_by')->nullable(); 
            $table->enum('status', ['pending', 'paid'])->default('pending'); 
            $table->date('paid_date')->nullable(); 
            $table->date('cheque_date'); 
            $table->decimal('amount', 15, 2); 
            $table->string('reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receiving_cheques');
    }
};
