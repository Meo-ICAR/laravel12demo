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
            // Drop company_id if it exists
            if (Schema::hasColumn('invoices', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }

            // Add new columns
            $table->string('fornitore_piva')->after('id')->nullable();
            $table->string('fornitore')->after('fornitore_piva')->nullable();
            $table->string('cliente_piva')->after('fornitore')->nullable();
            $table->string('cliente')->after('cliente_piva')->nullable();

            // Change invoice_number to string if it's not already
            if (Schema::hasColumn('invoices', 'invoice_number')) {
                $table->string('invoice_number')->change();
            }

            // Change invoice_date to datetime if it's not already
            if (Schema::hasColumn('invoices', 'invoice_date')) {
                $table->dateTime('invoice_date')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn([
                'fornitore_piva',
                'fornitore',
                'cliente_piva',
                'cliente'
            ]);

            // Revert invoice_number to integer
            $table->integer('invoice_number')->change();

            // Revert invoice_date to date
            $table->date('invoice_date')->change();
        });
    }
};
