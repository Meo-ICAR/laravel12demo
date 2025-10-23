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
        Schema::create('enasarcos', function (Blueprint $table) {
            $table->id();
            $table->enum('enasarco', ['monomandatario', 'plurimandatario']);
            $table->year('competenza')->default(date('Y'));
            $table->decimal('minimo', 10, 2);
            $table->decimal('massimo', 10, 2);
            $table->decimal('minimale', 10, 2);
            $table->decimal('massimale', 10, 2);
            $table->timestamps();

            // Add index for better performance on frequently queried fields
            $table->index(['enasarco', 'competenza']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enasarcos');
    }
};
