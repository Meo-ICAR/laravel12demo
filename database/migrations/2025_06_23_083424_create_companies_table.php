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
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('piva', 255)->nullable();
            $table->string('crm', 255)->nullable();
            $table->string('callcenter', 255)->nullable();
            $table->unsignedBigInteger('updated_by')->nullable()->index('companies_updated_by_foreign');
            $table->unsignedBigInteger('deleted_by')->nullable()->index('companies_deleted_by_foreign');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
