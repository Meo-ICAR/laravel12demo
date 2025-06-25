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
        Schema::table('proformas', function (Blueprint $table) {
            $table->string('stato', 255)->default('Inserito')->after('id');
            $table->decimal('contributo', 15, 2)->nullable()->after('compenso_descrizione');
            $table->string('contributo_descrizione', 255)->nullable()->after('contributo');
            $table->text('compenso_descrizione')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->dropColumn(['stato', 'contributo', 'contributo_descrizione']);
            $table->string('compenso_descrizione', 255)->change();
        });
    }
};
