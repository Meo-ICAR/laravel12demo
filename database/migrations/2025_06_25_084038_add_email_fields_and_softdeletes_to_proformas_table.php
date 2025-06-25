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
        Schema::table('proformas', function (Blueprint $table) {
            $table->string('emailsubject', 255)->nullable()->after('paid_at');
            $table->text('emailbody')->nullable()->after('emailsubject');
            $table->string('emailto', 255)->nullable()->after('emailbody');
            $table->string('emailfrom', 255)->nullable()->after('emailto');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->dropColumn(['emailsubject', 'emailbody', 'emailto', 'emailfrom', 'deleted_at']);
        });
    }
};
