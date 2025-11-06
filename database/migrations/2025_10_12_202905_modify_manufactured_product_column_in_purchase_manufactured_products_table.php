<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Clean invalid values before altering
        DB::statement("UPDATE purchase_manufactured_products 
                       SET manufactured_product = NULL 
                       WHERE manufactured_product NOT IN (0, 1)");

        // Step 2: Modify the column to be nullable boolean (tinyint(1))
        Schema::table('purchase_manufactured_products', function (Blueprint $table) {
            $table->boolean('manufactured_product')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_manufactured_products', function (Blueprint $table) {
            $table->boolean('manufactured_product')->default(0)->change();
        });
    }
};
