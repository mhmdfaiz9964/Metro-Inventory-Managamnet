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
        Schema::create('receiving_payments', function (Blueprint $table) {
            $table->id();
            $table->string('reason'); 
            $table->date('paid_date');
            $table->string('paid_by');
            $table->enum('status', ['paid', 'pending'])->default('pending'); 
            $table->string('bank_id');
            $table->decimal('amount', 15, 2); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receiving_payments');
    }
};
