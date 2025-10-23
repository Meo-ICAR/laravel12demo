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
        Schema::table('enasarcos', function (Blueprint $table) {
            $table->decimal('aliquota_soc', 5, 2)->default(0)->comment('Aliquota percentuale a carico della società');
            $table->decimal('aliquota_agente', 5, 2)->default(0)->comment('Aliquota percentuale a carico dell\'agente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enasarcos', function (Blueprint $table) {
            $table->dropColumn(['aliquota_soc', 'aliquota_agente']);
        });
    }
};
