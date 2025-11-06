<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bom_stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_stock_id')->constrained()->onDelete('cascade');
            $table->enum('adjustment_type', ['increase', 'decrease']);
            $table->enum('reason_type', ['damage', 'stock take', 'correction']);
            $table->integer('quantity');
            $table->unsignedBigInteger('adjusted_by')->nullable();
            $table->timestamps();

            $table->foreign('adjusted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_stock_adjustments');
    }
};
