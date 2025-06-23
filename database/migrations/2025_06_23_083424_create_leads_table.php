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
        Schema::create('leads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('legacy_id', 20)->nullable()->index();
            $table->string('campagna', 100)->nullable()->index();
            $table->string('lista', 100)->nullable()->index();
            $table->string('ragione_sociale', 255)->nullable();
            $table->string('cognome', 100)->nullable()->index();
            $table->string('nome', 100)->nullable()->index();
            $table->string('telefono', 20)->nullable()->index();
            $table->string('ultimo_operatore', 255)->nullable();
            $table->string('esito', 100)->nullable()->index();
            $table->dateTime('data_richiamo')->nullable();
            $table->string('operatore_richiamo', 255)->nullable();
            $table->dateTime('scadenza_anagrafica')->nullable();
            $table->string('indirizzo1', 255)->nullable();
            $table->string('indirizzo2', 255)->nullable();
            $table->string('indirizzo3', 255)->nullable();
            $table->string('comune', 100)->nullable()->index();
            $table->string('provincia', 10)->nullable()->index();
            $table->string('cap', 10)->nullable();
            $table->string('regione', 100)->nullable();
            $table->string('paese', 100)->nullable();
            $table->string('email', 255)->nullable()->index();
            $table->string('p_iva', 50)->nullable();
            $table->string('codice_fiscale', 20)->nullable();
            $table->string('telefono2', 20)->nullable();
            $table->string('telefono3', 20)->nullable();
            $table->string('telefono4', 20)->nullable();
            $table->string('sesso', 10)->nullable();
            $table->text('nota')->nullable();
            $table->boolean('attivo')->default(true)->index();
            $table->string('altro1', 255)->nullable();
            $table->string('altro2', 255)->nullable();
            $table->string('altro3', 255)->nullable();
            $table->string('altro4', 255)->nullable();
            $table->string('altro5', 255)->nullable();
            $table->string('altro6', 255)->nullable();
            $table->string('altro7', 255)->nullable();
            $table->string('altro8', 255)->nullable();
            $table->string('altro9', 255)->nullable();
            $table->string('altro10', 255)->nullable();
            $table->integer('chiamate')->default(0);
            $table->dateTime('ultima_chiamata')->nullable()->index();
            $table->string('creato_da', 255)->nullable();
            $table->string('durata_ultima_chiamata', 20)->nullable();
            $table->string('totale_durata_chiamate', 20)->nullable();
            $table->integer('chiamate_giornaliere')->default(0);
            $table->integer('chiamate_mensili')->default(0);
            $table->dateTime('data_creazione')->nullable();
            $table->string('company_id', 36)->nullable()->default('5c044917-15b3-4471-90c9-38061fcca754')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
