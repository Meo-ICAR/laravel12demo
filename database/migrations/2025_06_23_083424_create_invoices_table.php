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
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fornitore_piva', 255)->nullable();
            $table->string('fornitore', 255)->nullable();
            $table->string('cliente_piva', 255)->nullable();
            $table->string('cliente', 255)->nullable();
            $table->string('invoice_number', 255);
            $table->dateTime('invoice_date');
            $table->decimal('total_amount', 10);
            $table->decimal('delta', 15)->nullable();
            $table->dateTime('sended_at')->nullable();
            $table->dateTime('sended2_at')->nullable();
            $table->decimal('tax_amount', 10);
            $table->string('currency', 3)->default('EUR');
            $table->string('payment_method', 255)->nullable();
            $table->string('status', 255)->default('imported');
            $table->date('paid_at')->nullable();
            $table->boolean('isreconiled')->default(false);
            $table->text('xml_data')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->uuid('company_id')->nullable()->default('5c044917-15b3-4471-90c9-38061fcca754')->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
