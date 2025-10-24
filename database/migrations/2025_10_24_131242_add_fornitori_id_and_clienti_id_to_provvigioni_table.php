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
        Schema::table('provvigioni', function (Blueprint $table) {
            // Use string(36) to match the UUID length
            $table->string('fornitori_id', 36)->nullable()->after('denominazione_riferimento');
            $table->string('clienti_id', 36)->nullable()->after('istituto_finanziario');

            // Add indexes first
            $table->index('fornitori_id');
            $table->index('clienti_id');

            // Add foreign key constraints with explicit column types
            $table->foreign('fornitori_id')
                  ->references('id')
                  ->on('fornitoris')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->foreign('clienti_id')
                  ->references('id')
                  ->on('clientis')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provvigioni', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['fornitori_id']);
            $table->dropForeign(['clienti_id']);
            
            // Then drop the columns
            $table->dropColumn('fornitori_id');
            $table->dropColumn('clienti_id');
        });
    }
};
