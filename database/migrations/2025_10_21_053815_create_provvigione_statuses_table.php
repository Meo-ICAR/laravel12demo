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
        Schema::create('provvigioni_status', function (Blueprint $table) {
            $table->string('id', 191)->nullable()->comment('Stato attuale della pratica');
            $table->boolean('isvalid')->default(1);
            
            // Add primary key
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provvigioni_status');
    }
};
