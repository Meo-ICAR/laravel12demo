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
            $table->foreign(['customertype_id'])->references(['id'])->on('customertypes')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientis', function (Blueprint $table) {
            $table->dropForeign('clientis_customertype_id_foreign');
        });
    }
};
