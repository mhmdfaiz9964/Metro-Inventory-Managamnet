<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bom_components', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->nullable()->after('product_id'); 
            $table->text('model')->nullable()->after('brand_id');

            // If you want foreign key relationship with brands table:
            // $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('bom_components', function (Blueprint $table) {
            $table->dropColumn(['brand_id', 'model']);
        });
    }
};
