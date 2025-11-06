<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix existing invalid values before altering the column
        DB::table('purchase_payments')
            ->whereNotIn('payment_method', ['cash', 'cheque', 'loan', 'fund_transfer'])
            ->update(['payment_method' => 'cash']);

        Schema::table('purchase_payments', function (Blueprint $table) {
            // Change enum to include loan and fund_transfer
            $table->enum('payment_method', ['cash', 'cheque', 'loan', 'fund_transfer'])
                ->default('cash')
                ->change();
        });
    }

    public function down(): void
    {
        // Revert to original enum
        DB::table('purchase_payments')
            ->whereNotIn('payment_method', ['cash', 'cheque'])
            ->update(['payment_method' => 'cash']);

        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'cheque'])
                ->default('cash')
                ->change();
        });
    }
};
