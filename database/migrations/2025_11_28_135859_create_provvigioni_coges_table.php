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
        Schema::create('provvigioni_coges', function (Blueprint $table) {
            $table->id();
            $table->date('data_invio')->nullable();
            $table->date('data_storno')->nullable();
            $table->foreignId('provvigioni_id')->constrained('provvigioni')->onDelete('cascade');
            $table->foreignId('coge_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Add composite unique key to prevent duplicate relationships
            $table->unique(['provvigioni_id', 'coge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provvigioni_coges');
    }
};
