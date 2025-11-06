<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reason');
            $table->string('bank_id'); // Source bank
            $table->string('to_bank_id')->nullable(); // Destination bank (nullable unless type = bank_to_bank)
            $table->enum('type', [
                'bank_to_bank',
                'outsource_payment',
                'employee_payment',
                'sales_payment'
            ]);
            $table->date('transfer_date');
            $table->unsignedBigInteger('transferred_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->enum('status', [
                'pending',
                'processing',
                'pending_for_approval',
                'completed'
            ])->default('pending');
            $table->text('note')->nullable();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_transfers');
    }
};
