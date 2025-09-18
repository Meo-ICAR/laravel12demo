<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pratiches', function (Blueprint $table) {
            // Strings
            $table->string('Pratica')->nullable();
            $table->string('Status_pratica')->nullable();
            $table->string('Cliente_ID')->nullable();
            $table->string('Codice_fiscale')->nullable();
            $table->string('Prodotto')->nullable();
            $table->string('Residenza_citta')->nullable();
            $table->string('Residenza_provincia')->nullable();
            $table->string('Regione')->nullable();

            // Numerics/Decimals
            $table->decimal('Importo_erogato', 15, 2)->nullable();
            $table->decimal('Importo', 15, 2)->nullable();
            $table->decimal('Totale_compensi_lordo', 15, 2)->nullable();
            $table->decimal('Totale_compensi_passivo', 15, 2)->nullable();
            $table->decimal('Totale_compensi_netto', 15, 2)->nullable();
            $table->decimal('Importo_compenso', 15, 2)->nullable();
            $table->decimal('Importo_compenso_euro', 15, 2)->nullable();
            $table->decimal('Importo_rata', 15, 2)->nullable();
            $table->integer('Durata')->nullable();
            $table->decimal('Montante', 15, 2)->nullable();
            $table->decimal('TAN', 8, 3)->nullable();
            $table->decimal('Importo_compenso2', 15, 2)->nullable();

            // Dates/Datetimes
            $table->date('Data_decorrenza')->nullable();
            $table->dateTime('Inserita_at')->nullable();
            $table->dateTime('Invio_in_istruttoria_at')->nullable();
            $table->dateTime('Deliberata_at')->nullable();
            $table->dateTime('Liquidata_at')->nullable();
            $table->dateTime('Perfezionata_at')->nullable();
            $table->dateTime('Declinata_at')->nullable();
            $table->dateTime('Pratica_respinta_at')->nullable();
            $table->dateTime('Rinuncia_cliente_at')->nullable();
            $table->dateTime('Data_firma_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pratiches', function (Blueprint $table) {
            $table->dropColumn([
                'Pratica',
                'Status_pratica',
                'Cliente_ID',
                'Codice_fiscale',
                'Prodotto',
                'Residenza_citta',
                'Residenza_provincia',
                'Regione',
                'Importo_erogato',
                'Importo',
                'Totale_compensi_lordo',
                'Totale_compensi_passivo',
                'Totale_compensi_netto',
                'Importo_compenso',
                'Importo_compenso_euro',
                'Importo_rata',
                'Durata',
                'Montante',
                'TAN',
                'Importo_compenso2',
                'Data_decorrenza',
                'Inserita_at',
                'Invio_in_istruttoria_at',
                'Deliberata_at',
                'Liquidata_at',
                'Perfezionata_at',
                'Declinata_at',
                'Pratica_respinta_at',
                'Rinuncia_cliente_at',
                'Data_firma_at',
            ]);
        });
    }
};
