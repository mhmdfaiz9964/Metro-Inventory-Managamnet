<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_payments', function (Blueprint $table) {
            $table->enum('discount_type', ['percentage', 'amount'])->default('amount')->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('sales_payments', function (Blueprint $table) {
            $table->dropColumn('discount_type');
        });
    }
};
