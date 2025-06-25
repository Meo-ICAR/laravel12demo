<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fornitoris', function (Blueprint $table) {
            $table->string('nomecoge', 255)->nullable()->after('cf');
            $table->string('nomefattura', 255)->nullable()->after('nomecoge');
        });
    }

    public function down(): void
    {
        Schema::table('fornitoris', function (Blueprint $table) {
            $table->dropColumn(['nomecoge', 'nomefattura']);
        });
    }
};
