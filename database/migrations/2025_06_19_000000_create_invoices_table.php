<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
             $table->string('fornitore', 255)->nullable()->after('id');
            $table->char('fornitore_piva', 16)->nullable()->after('fornitore');
            $table->string('cliente', 255)->nullable()->after('fornitore_piva');
            $table->char('cliente_piva', 16)->nullable()->after('cliente');
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('tax_amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('payment_method')->nullable();
            $table->string('status')->default('imported');
            $table->text('xml_data')->nullable();
            $table->timestamps();


        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
