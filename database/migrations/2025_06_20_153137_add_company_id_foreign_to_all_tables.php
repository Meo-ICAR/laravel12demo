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
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });
        Schema::table('clientis', function (Blueprint $table) {
            $table->char('company_id', 36)->nullable()->change();
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });
        Schema::table('employroles', function (Blueprint $table) {
            $table->char('company_id', 36)->nullable()->change();
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fornitoris', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
        Schema::table('clientis', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
        Schema::table('employroles', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
    }
};
