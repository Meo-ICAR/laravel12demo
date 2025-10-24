<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Provvigione;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
/*
    create a command that for every invoice ordered by invoice_ste with status 'imported' selects all  provvigioni with stato 'proforma'joined by fornitori_id and if the sum/importo) of the selected provvigioni is equal to invoices->total_amount changes the invoice->status in 'Reconcilied' and the provvigioni->stato in 'Abbinato', provvigioni->invoice_number = invoice->id
*/
class ReconcileInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:reconcile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile imported invoices with provvigioni';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting invoice reconciliation process...');

        // Get all imported invoices ordered by date
        $invoices = Invoice::where('status', 'imported')
            ->orderBy('invoice_date')
            ->get();

        $reconciledCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($invoices as $invoice) {
            try {
                // Skip if no fornitore is associated
                if (!$invoice->fornitori_id) {
                    $this->warn("Skipping invoice {$invoice->invoice_number}: No fornitore associated");
                    $skippedCount++;
                    continue;
                }
                $datainvio = $invoice->invoice_date;
                // Get all provvigioni for this fornitore with stato 'proforma' and no invoice number
                $provvigioni = Provvigione::where('fornitori_id', $invoice->fornitori_id)
                    ->where('stato', 'Proforma')
                    ->where('data_inserimento_compenso', '<=', $datainvio)
                    ->whereNull('invoice_number')
                    ->orderBy('data_inserimento_compenso', 'desc')
                    ->get();

                if ($provvigioni->isEmpty()) {
                    $this->info("No provvigioni found for invoice {$invoice->invoice_number}");
                    $skippedCount++;
                    continue;
                }

                $totalProvvigioni = $provvigioni->sum('importo');

                // Check if the amounts match (with a small tolerance for floating point comparison)
                if (abs($totalProvvigioni - $invoice->total_amount) < 1) {
                    DB::beginTransaction();

                    try {
                        // Update invoice status
                        $invoice->update([
                            'status' => 'Reconciled',
                            'isreconiled' => true
                        ]);

                        // Update provvigioni
                        Provvigione::whereIn('id', $provvigioni->pluck('id'))
                            ->update([
                                'stato' => 'Abbinato',
                                'invoice_number' => $invoice->invoice_number
                            ]);

                        DB::commit();
                        $this->info("Successfully reconciled invoice {$invoice->invoice_number} with {$provvigioni->count()} provvigioni");
                        $reconciledCount++;
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error("Error reconciling invoice {$invoice->invoice_number}: " . $e->getMessage());
                        $errorCount++;
                        Log::error("Error reconciling invoice {$invoice->invoice_number}", [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                } else {
                    $this->warn("Amount mismatch for invoice {$invoice->invoice_number}: " .
                        "Invoice: {$invoice->total_amount} vs Provvigioni: {$totalProvvigioni}");
                    $skippedCount++;
                }
            } catch (\Exception $e) {
                $this->error("Error processing invoice {$invoice->invoice_number}: " . $e->getMessage());
                $errorCount++;
                Log::error("Error processing invoice {$invoice->invoice_number}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("\nReconciliation completed:");
        $this->info("- Reconciled: {$reconciledCount} invoices");
        $this->info("- Skipped: {$skippedCount} invoices");
        $this->info("- Errors: {$errorCount} invoices");

        return 0;
    }
}
