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
            $table->date('data_pagamento')->nullable()->after('paided_at');
            $table->string('n_fattura', 20)->nullable()->after('data_pagamento');
            $table->date('data_fattura')->nullable()->after('n_fattura');
            $table->date('data_status')->nullable()->after('data_fattura');
            $table->string('piva', 20)->nullable()->after('data_status');
            $table->string('cf', 20)->nullable()->after('piva');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provvigioni', function (Blueprint $table) {
            $table->dropColumn([
                'data_pagamento',
                'n_fattura',
                'data_fattura',
                'data_status',
                'piva',
                'cf'
            ]);
        });
    }
};
