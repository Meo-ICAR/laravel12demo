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
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the existing column if it exists
            if (Schema::hasColumn('invoices', 'clienti_id')) {
                $table->dropColumn('clienti_id');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            // Add the column with correct UUID type
            $table->string('clienti_id', 36)->nullable()->after('id');
            $table->foreign('clienti_id')->references('id')->on('clientis')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['clienti_id']);
            $table->dropColumn('clienti_id');
        });
    }
};
