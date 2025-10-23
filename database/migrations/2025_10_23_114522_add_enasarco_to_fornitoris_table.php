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
        Schema::table('fornitoris', function (Blueprint $table) {
            $table->enum('enasarco', ['no', 'monomandatario', 'plurimandatario'])->nullable()->after('anticipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fornitoris', function (Blueprint $table) {
            $table->dropColumn('enasarco');
        });
    }
};
