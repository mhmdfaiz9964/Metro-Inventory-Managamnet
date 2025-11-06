<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_manufactured_products', function (Blueprint $table) {
            $table->id();
            $table->string('manufactured_product');
            $table->decimal('total_price', 15, 2)->default(0);
            $table->enum('discount_type', ['percentage', 'amount'])->nullable();
            $table->decimal('discount', 15, 2)->default(0);
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();

            // Foreign key if supplier model exists
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_manufactured_products');
    }
};
