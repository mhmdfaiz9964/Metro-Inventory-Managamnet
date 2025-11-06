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
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->enum('payment_method', ['cash', 'cheque'])->default('cash');
            $table->decimal('payment_amount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0); // Discount amount
            $table->enum('discount_type', ['amount', 'percentage'])->default('amount'); // Discount type
            $table->date('payment_date');
            $table->foreignId('bank_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};
