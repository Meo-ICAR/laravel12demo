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
        Schema::create('proformas', function (Blueprint $table) {
            $table->id();
            $table->char('company_id', 36)->nullable();
            $table->char('fornitori_id', 36)->nullable();
            $table->decimal('anticipo', 15, 2)->nullable();
            $table->string('anticipo_descrizione', 255)->nullable();
            $table->decimal('compenso', 15, 2)->nullable();
            $table->string('compenso_descrizione', 255)->nullable();
            $table->text('annotation')->nullable();
            $table->dateTime('sended_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->decimal('delta', 15, 2)->nullable();
            $table->text('delta_annotation')->nullable();
            $table->timestamps();

            // Foreign keys removed due to type/collation issues
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proformas');
    }
};
