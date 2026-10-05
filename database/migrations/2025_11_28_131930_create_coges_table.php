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
        Schema::create('coges', function (Blueprint $table) {
            $table->id();
            $table->string('fonte', 255);
            $table->string('conto_dare', 255);
            $table->string('descrizione_dare', 255);
            $table->string('conto_avere', 255);
            $table->string('descrizione_avere', 255);
            $table->string('annotazioni', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coges');
    }
};
