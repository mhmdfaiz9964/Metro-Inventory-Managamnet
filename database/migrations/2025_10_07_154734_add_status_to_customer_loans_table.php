<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customer_loans', function (Blueprint $table) {
            $table->enum('status', [
                'proceeding',         // 1
                'given_to_customer',  // 2
                'waiting_due_date',   // 3
                'customer_paid'       // 4
            ])->default('proceeding')->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('customer_loans', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
