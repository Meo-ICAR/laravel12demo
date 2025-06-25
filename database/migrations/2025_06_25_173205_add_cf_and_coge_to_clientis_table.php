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
        Schema::table('clientis', function (Blueprint $table) {
            $table->string('cf', 255)->nullable()->after('id');
            $table->string('coge', 255)->nullable()->after('cf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientis', function (Blueprint $table) {
            $table->dropColumn(['cf', 'coge']);
        });
    }
};
