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
        Schema::create('pratica_compensos', function (Blueprint $table) {
            $table->string('id', 255)->comment('Descrizione della provvigione');
            $table->integer('iscoordinamento')->default(0);
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pratica_compensos');
    }
};
