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
        Schema::create('sales_payments', function (Blueprint $table) {
            $table->id();

            // Link to sale
            $table->foreignId('sale_id')
                  ->constrained('sales')
                  ->onDelete('cascade');

            // Payment method (cash, cheque, etc.)
            $table->enum('payment_method', ['cash', 'cheque']);

            // Financial fields
            $table->decimal('payment_amount', 12, 2)->default(0); // total to pay
            $table->decimal('discount', 12, 2)->default(0);       // any discount
            $table->decimal('payment_paid', 12, 2)->default(0);   // amount actually paid

            // Who paid
            $table->string('paid_by')->nullable();

            // When paid
            $table->date('paid_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_payments');
    }
};
