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
            $table->string('nome')->nullable()->after('name');
            $table->date('natoil')->nullable()->after('nome');
            $table->string('indirizzo')->nullable()->after('natoil');
            $table->string('comune')->nullable()->after('indirizzo');
            $table->string('cap')->nullable()->after('comune');
            $table->string('prov')->nullable()->after('cap');
            $table->string('tel')->nullable()->after('prov');
            $table->string('coordinatore')->nullable()->after('tel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fornitoris', function (Blueprint $table) {
            $table->dropColumn([
                'nome',
                'natoil',
                'indirizzo',
                'comune',
                'cap',
                'prov',
                'tel',
                'coordinatore'
            ]);
        });
    }
};
