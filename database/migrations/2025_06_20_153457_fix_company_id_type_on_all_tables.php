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
        Schema::table('fornitoris', function (Blueprint $table) {
            $table->char('company_id', 36)->nullable()->change();
        });
        Schema::table('clientis', function (Blueprint $table) {
            $table->char('company_id', 36)->nullable()->change();
        });
        Schema::table('employroles', function (Blueprint $table) {
            $table->char('company_id', 36)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration for type change
    }
};
