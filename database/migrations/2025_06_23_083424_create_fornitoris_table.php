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
        Schema::create('fornitoris', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('codice', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('nome', 255)->nullable();
            $table->date('natoil')->nullable();
            $table->string('indirizzo', 255)->nullable();
            $table->string('comune', 255)->nullable();
            $table->string('cap', 255)->nullable();
            $table->string('prov', 255)->nullable();
            $table->string('tel', 255)->nullable();
            $table->string('coordinatore', 255)->nullable();
            $table->string('piva', 16)->nullable();
            $table->string('email', 255)->nullable();
            $table->decimal('anticipo', 15)->nullable();
            $table->tinyInteger('issubfornitore')->default(0);
            $table->string('operatore', 255)->nullable();
            $table->boolean('iscollaboratore')->nullable();
            $table->boolean('isdipendente')->nullable();
            $table->string('regione', 255)->nullable();
            $table->string('citta', 255)->nullable();
            $table->char('company_id', 36)->nullable()->default('5c044917-15b3-4471-90c9-38061fcca754')->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fornitoris');
    }
};
