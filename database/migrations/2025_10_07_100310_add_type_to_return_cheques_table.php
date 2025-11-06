<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('return_cheques', function (Blueprint $table) {
            $table->enum('type', ['Cheque in Hand', 'Cheque It Out'])
                  ->default('Cheque in Hand')
                  ->after('return_cheque_no'); // adjust position if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_cheques', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
