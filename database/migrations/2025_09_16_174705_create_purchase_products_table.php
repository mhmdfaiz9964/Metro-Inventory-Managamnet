<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_products', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_id');
            $table->string('product_id');
            $table->string('code')->nullable();
            $table->decimal('cost_price', 12, 2);
            $table->decimal('regular_price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->integer('qty')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_products');
    }
};
