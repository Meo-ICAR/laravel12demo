<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fornitoris', function (Blueprint $table) {
            $table->char('id', 36);
            $table->string('codice')->nullable()->collation('utf8mb4_bin');
            $table->string('name')->nullable()->collation('utf8mb4_bin');
            $table->string('piva', 16)->nullable()->collation('utf8mb4_bin');
            $table->string('email')->nullable()->collation('utf8mb4_bin');
            $table->string('operatore')->nullable()->collation('utf8mb4_bin');
            $table->string('iscollaboratore')->nullable()->collation('utf8mb4_bin');
            $table->string('isdipendente')->nullable()->collation('utf8mb4_bin');
            $table->string('regione')->nullable()->collation('utf8mb4_bin');
            $table->string('citta')->nullable()->collation('utf8mb4_bin');
            $table->char('company_id', 36)->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable();
           $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamps();

            $table->primary('id');
            $table->index('company_id', 'fornitoris_company_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fornitoris');
    }
};
