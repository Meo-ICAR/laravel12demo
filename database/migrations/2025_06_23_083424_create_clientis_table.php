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
        Schema::create('clientis', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('codice', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('piva', 16)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('regione', 255)->nullable();
            $table->string('citta', 255)->nullable();
            $table->char('company_id', 36)->nullable()->default('5c044917-15b3-4471-90c9-38061fcca754')->index();
            $table->unsignedBigInteger('customertype_id')->nullable()->index('clientis_customertype_id_foreign');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientis');
    }
};
