<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manufactures', function (Blueprint $table) {
            $table->integer('quantity_to_produce')->default(0)->after('status');
            $table->integer('quantity_produced')->default(0)->after('quantity_to_produce');
            
            // Optional costing fields
            $table->decimal('material_cost', 12, 2)->nullable()->after('quantity_produced');
            $table->decimal('labor_cost', 12, 2)->nullable()->after('material_cost');
            $table->decimal('overhead_cost', 12, 2)->nullable()->after('labor_cost');
            $table->decimal('total_cost', 12, 2)->nullable()->after('overhead_cost');
            $table->decimal('unit_cost', 12, 2)->nullable()->after('total_cost');
        });
    }

    public function down(): void
    {
        Schema::table('manufactures', function (Blueprint $table) {
            $table->dropColumn([
                'quantity_to_produce',
                'quantity_produced',
                'material_cost',
                'labor_cost',
                'overhead_cost',
                'total_cost',
                'unit_cost',
            ]);
        });
    }
};
