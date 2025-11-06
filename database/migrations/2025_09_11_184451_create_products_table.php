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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); 
            $table->string('name');
            $table->string('code')->unique();
            $table->string('product_category_id');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->string('image')->nullable();
            $table->text('description')->nullable();

            $table->decimal('regular_price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();

            $table->enum('warranty', [
                'No warranty',
                '1 month',
                '3 months',
                '6 months',
                '12 months',
                '24 months',
            ])->default('No warranty');

            $table->decimal('weight', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
