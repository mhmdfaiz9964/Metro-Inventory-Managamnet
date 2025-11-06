<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_brand_id')->nullable()->after('id');
            $table->text('model')->nullable()->after('product_brand_id');

            // If you want a foreign key relation with product_brands:
            // $table->foreign('product_brand_id')
            //       ->references('id')
            //       ->on('product_brands')
            //       ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_brand_id', 'model']);
        });
    }
};
