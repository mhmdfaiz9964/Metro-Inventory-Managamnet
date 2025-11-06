<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->default(0)->after('payment_status');
            $table->decimal('paid_amount', 15, 2)->nullable()->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['total_amount', 'paid_amount']);
        });
    }
};
