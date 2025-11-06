<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bom_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_component_id')->constrained('bom_components')->onDelete('cascade');
            $table->decimal('available_stock', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bom_stocks');
    }
};
