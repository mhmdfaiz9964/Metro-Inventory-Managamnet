<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receiving_cheques', function (Blueprint $table) {
            if (Schema::hasColumn('receiving_cheques', 'bank')) {
                $table->dropColumn('bank');
            }

            // Create bank_id as string instead of foreign key
            $table->string('bank_id')->after('cheque_no')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('receiving_cheques', function (Blueprint $table) {
            $table->dropColumn('bank_id');

            $table->string('bank')->nullable();
        });
    }
};
