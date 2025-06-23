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
        Schema::create('calls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('numero_chiamato', 20)->nullable()->index();
            $table->dateTime('data_inizio')->nullable()->index();
            $table->string('durata', 10)->nullable();
            $table->string('stato_chiamata', 50)->nullable()->index();
            $table->string('esito', 100)->nullable()->index();
            $table->string('utente', 255)->nullable()->index();
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
        Schema::dropIfExists('calls');
    }
};
