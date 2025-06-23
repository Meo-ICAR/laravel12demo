<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provvigioni', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('data_inserimento_compenso')->nullable();
            $table->string('descrizione')->nullable();
            $table->string('tipo')->nullable();
            $table->decimal('importo', 15, 2)->nullable();
            $table->decimal('importo_effettivo', 15, 2)->nullable();
            $table->string('quota')->nullable();
            $table->string('stato')->nullable();
            $table->string('denominazione_riferimento')->nullable();
            $table->string('entrata_uscita')->nullable();
            $table->string('cognome')->nullable();
            $table->string('nome')->nullable();
            $table->string('segnalatore')->nullable();
            $table->string('fonte')->nullable();
            $table->string('id_pratica')->nullable();
            $table->string('tipo_pratica')->nullable();
            $table->date('data_inserimento_pratica')->nullable();
            $table->date('data_stipula')->nullable();
            $table->string('istituto_finanziario')->nullable();
            $table->string('prodotto')->nullable();
            $table->string('macrostatus')->nullable();
            $table->string('status_pratica')->nullable();
            $table->date('data_status_pratica')->nullable();
            $table->decimal('montante', 15, 2)->nullable();
            $table->decimal('importo_erogato', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provvigioni');
    }
};
