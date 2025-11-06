<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manufacture_items', function (Blueprint $table) {
            $table->integer('issued_qty')->default(0)->after('required_qty');
            $table->integer('consumed_qty')->default(0)->after('issued_qty');
        });
    }

    public function down(): void
    {
        Schema::table('manufacture_items', function (Blueprint $table) {
            $table->dropColumn(['issued_qty', 'consumed_qty']);
        });
    }
};
