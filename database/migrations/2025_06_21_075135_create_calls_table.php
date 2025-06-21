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
            $table->id();
            $table->string('numero_chiamato', 20)->nullable();
            $table->datetime('data_inizio')->nullable();
            $table->string('durata', 10)->nullable(); // Format: HH:MM or MM:SS
            $table->string('stato_chiamata', 50)->nullable();
            $table->string('esito', 100)->nullable();
            $table->string('utente', 255)->nullable();
            $table->string('company_id', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index('numero_chiamato');
            $table->index('data_inizio');
            $table->index('stato_chiamata');
            $table->index('esito');
            $table->index('utente');
            $table->index('company_id');
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
