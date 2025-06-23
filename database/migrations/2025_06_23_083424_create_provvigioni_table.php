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
        Schema::create('provvigioni', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('legacy_id', 255)->nullable();
            $table->date('data_inserimento_compenso')->nullable();
            $table->string('descrizione', 255)->nullable();
            $table->string('tipo', 255)->nullable();
            $table->decimal('importo', 15)->nullable();
            $table->string('invoice_number', 255)->nullable();
            $table->decimal('importo_effettivo', 15)->nullable();
            $table->string('quota', 255)->nullable();
            $table->string('stato', 255)->nullable();
            $table->string('denominazione_riferimento', 255)->nullable();
            $table->string('entrata_uscita', 255)->nullable();
            $table->string('cognome', 255)->nullable();
            $table->string('nome', 255)->nullable();
            $table->string('segnalatore', 255)->nullable();
            $table->string('fonte', 255)->nullable();
            $table->string('id_pratica', 255)->nullable();
            $table->string('tipo_pratica', 255)->nullable();
            $table->date('data_inserimento_pratica')->nullable();
            $table->date('data_stipula')->nullable();
            $table->string('istituto_finanziario', 255)->nullable();
            $table->string('prodotto', 255)->nullable();
            $table->string('macrostatus', 255)->nullable();
            $table->string('status_pratica', 255)->nullable();
            $table->date('data_status_pratica')->nullable();
            $table->decimal('montante', 15)->nullable();
            $table->decimal('importo_erogato', 15)->nullable();
            $table->timestamps();
            $table->timestamp('sended_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('paided_at')->nullable();
            $table->uuid('company_id')->nullable()->default('5c044917-15b3-4471-90c9-38061fcca754')->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provvigioni');
    }
};
