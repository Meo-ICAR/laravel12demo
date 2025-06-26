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
        Schema::create('pratiches', function (Blueprint $table) {
            $table->id();
            $table->string('pratica_id')->unique(); // Primary identifier from CSV
            $table->date('Data_inserimento')->nullable();
            $table->text('Descrizione')->nullable();
            $table->string('Cliente')->nullable();
            $table->string('Agente')->nullable();
            $table->string('Segnalatore')->nullable();
            $table->string('Fonte')->nullable();
            $table->string('Tipo')->nullable();
            $table->string('Istituto_finanziario')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pratiches');
    }
};
