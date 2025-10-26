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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('importo_iva', 10, 2)->nullable()->after('tax_amount');
            $table->decimal('importo_totale_fornitore', 10, 2)->nullable()->after('importo_iva');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['importo_iva', 'importo_totale_fornitore']);
        });
    }
};
