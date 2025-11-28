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
        Schema::table('provvigioni_statos', function (Blueprint $table) {
            $table->boolean('is_stornato')->default(false)->after('stato');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provvigioni_statos', function (Blueprint $table) {
            $table->dropColumn('is_stornato');
        });
    }
};
