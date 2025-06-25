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
            $table->decimal('contributo', 15, 2)->nullable()->after('anticipo');
            $table->string('contributo_description', 255)->default('Contributo spese')->after('contributo');
            $table->string('anticipo_description', 255)->default('Anticipo attuale')->after('contributo_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fornitoris', function (Blueprint $table) {
            $table->dropColumn(['contributo', 'contributo_description', 'anticipo_description']);
        });
    }
};
