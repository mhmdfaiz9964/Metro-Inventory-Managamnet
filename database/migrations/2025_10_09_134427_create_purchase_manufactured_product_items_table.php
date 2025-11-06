<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_manufactured_product_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_manufactured_product_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('qty', 15, 2)->default(0);
            $table->enum('discount_type', ['percentage', 'amount'])->nullable();
            $table->decimal('discount', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('purchase_manufactured_product_id', 'pm_product_fk')
                ->references('id')->on('purchase_manufactured_products')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_manufactured_product_items');
    }
};
