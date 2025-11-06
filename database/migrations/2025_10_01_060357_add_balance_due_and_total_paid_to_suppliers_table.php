<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->decimal('balance_due', 15, 2)->default(0)->after('status');
            $table->decimal('total_paid', 15, 2)->default(0)->after('balance_due');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['balance_due', 'total_paid']);
        });
    }
};
