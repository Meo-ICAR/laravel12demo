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
            $table->timestamp('sended_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('paided_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provvigioni', function (Blueprint $table) {
            $table->dropColumn(['sended_at', 'received_at', 'paided_at']);
        });
    }
};
