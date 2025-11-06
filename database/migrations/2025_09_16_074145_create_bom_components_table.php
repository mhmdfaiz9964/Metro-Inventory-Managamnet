<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_components', function (Blueprint $table) {
            $table->id();
            $table->string('product_id');
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->decimal('quantity_required', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_components');
    }
};
