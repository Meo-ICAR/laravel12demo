<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Fornitori;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Transfer invoiceins to invoices table
     */
    public function transferInvoiceinsToInvoices()
    {
        $invoicesImported = 0;
        $invoicesSkipped = 0;
        $errors = [];

        try {
            // Get invoiceins that have matching fornitoris
            $invoiceins = \App\Models\Invoicein::join('fornitoris', 'fornitoris.coge', '=', 'invoiceins.nr_cliente_fornitore')
                ->select('invoiceins.*')
                ->get();

            Log::info("Starting transfer of invoiceins to invoices", [
                'total_eligible_invoiceins' => $invoiceins->count()
            ]);

            foreach ($invoiceins as $invoicein) {
                try {
                    $result = $this->createInvoiceFromInvoicein($invoicein);
                    if ($result['success']) {
                        $invoicesImported++;
                        Log::info("Successfully created invoice from invoicein", [
                            'invoicein_id' => $invoicein->id,
                            'invoice_number' => $invoicein->nr_documento
                        ]);
                    } else {
                        $invoicesSkipped++;
                        Log::info("Skipped invoicein", [
                            'invoicein_id' => $invoicein->id,
                            'invoice_number' => $invoicein->nr_documento,
                            'reason' => $result['reason']
                        ]);
                        if ($result['reason']) {
                            $errors[] = $result['reason'];
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error processing invoicein {$invoicein->nr_documento}: " . $e->getMessage();
                    Log::error("Error processing invoicein", [
                        'invoicein_id' => $invoicein->id,
                        'invoice_number' => $invoicein->nr_documento,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("Transfer completed", [
                'imported' => $invoicesImported,
                'skipped' => $invoicesSkipped,
                'errors_count' => count($errors)
            ]);

        } catch (\Exception $e) {
            $errors[] = "Error importing to invoices: " . $e->getMessage();
            Log::error("Error in transferInvoiceinsToInvoices", [
                'error' => $e->getMessage()
            ]);
        }

        return [
            'imported' => $invoicesImported,
            'skipped' => $invoicesSkipped,
            'errors' => $errors
        ];
    }

    /**
     * Create invoice from invoicein data
     */
    public function createInvoiceFromInvoicein($invoicein)
    {
        // Check if invoice with same nr_documento already exists
        if (Invoice::where('invoice_number', $invoicein->nr_documento)->exists()) {
            return [
                'success' => false,
                'reason' => "Invoice with number {$invoicein->nr_documento} already exists"
            ];
        }

        // Validate required fields
        if (empty($invoicein->nr_documento)) {
            return [
                'success' => false,
                'reason' => "Invoice number is required"
            ];
        }

        try {
            // Create new invoice
            $invoice = new Invoice([
                'fornitore_piva' => $invoicein->partita_iva,
                'fornitore' => $invoicein->nome_fornitore,
                'invoice_number' => $invoicein->nr_documento,
                'invoice_date' => $invoicein->data_ora_invio_ricezione,
                'total_amount' => $invoicein->importo_totale_fornitore,
                'tax_amount' => $invoicein->importo_iva,
                'coge' => $invoicein->nr_cliente_fornitore,
                'status' => 'imported',
                'currency' => 'EUR',
            ]);

            $invoice->save();

            Log::info("Created invoice from invoicein", [
                'invoice_number' => $invoice->invoice_number,
                'fornitore' => $invoice->fornitore
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error("Error creating invoice from invoicein", [
                'invoicein_id' => $invoicein->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'reason' => "Database error: " . $e->getMessage()
            ];
        }
    }

    /**
     * Create invoice from fornitore data
     */
    public function createInvoiceFromFornitore($fornitore, $invoiceData)
    {
        // Check if invoice with same invoice_number already exists
        if (!empty($invoiceData['invoice_number']) &&
            Invoice::where('invoice_number', $invoiceData['invoice_number'])->exists()) {
            return [
                'success' => false,
                'reason' => "Invoice with number {$invoiceData['invoice_number']} already exists"
            ];
        }

        // Validate required fields
        if (empty($invoiceData['invoice_number'])) {
            return [
                'success' => false,
                'reason' => "Invoice number is required"
            ];
        }

        try {
            // Create new invoice
            $invoice = new Invoice([
                'fornitore_piva' => $fornitore->piva,
                'fornitore' => $fornitore->name,
                'invoice_number' => $invoiceData['invoice_number'],
                'invoice_date' => $invoiceData['invoice_date'] ?? now(),
                'total_amount' => $invoiceData['total_amount'] ?? 0,
                'tax_amount' => $invoiceData['tax_amount'] ?? 0,
                'coge' => $fornitore->coge,
                'status' => $invoiceData['status'] ?? 'imported',
                'currency' => $invoiceData['currency'] ?? 'EUR',
                'payment_method' => $invoiceData['payment_method'] ?? null,
            ]);

            $invoice->save();

            Log::info("Created invoice from fornitore", [
                'invoice_number' => $invoice->invoice_number,
                'fornitore' => $invoice->fornitore
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error("Error creating invoice from fornitore", [
                'fornitore_id' => $fornitore->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'reason' => "Database error: " . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk create invoices from fornitoris data
     */
    public function createInvoicesFromFornitoris($invoicesData)
    {
        $invoicesImported = 0;
        $invoicesSkipped = 0;
        $errors = [];

        foreach ($invoicesData as $data) {
            try {
                // Find fornitore by coge
                $fornitore = Fornitori::where('coge', $data['coge'] ?? null)->first();

                if (!$fornitore) {
                    $invoicesSkipped++;
                    $errors[] = "Fornitore with coge {$data['coge']} not found";
                    continue;
                }

                $result = $this->createInvoiceFromFornitore($fornitore, $data);
                if ($result['success']) {
                    $invoicesImported++;
                } else {
                    $invoicesSkipped++;
                    if ($result['reason']) {
                        $errors[] = $result['reason'];
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Error processing invoice data: " . $e->getMessage();
            }
        }

        return [
            'imported' => $invoicesImported,
            'skipped' => $invoicesSkipped,
            'errors' => $errors
        ];
    }

    /**
     * Export invoices to CSV/Excel
     */
    public function exportInvoices($format = 'csv', $filters = [])
    {
        $query = Invoice::query();

        // Apply filters
        if (!empty($filters['fornitore'])) {
            $query->where('fornitore', 'like', '%' . $filters['fornitore'] . '%');
        }

        if (!empty($filters['invoice_number'])) {
            $query->where('invoice_number', 'like', '%' . $filters['invoice_number'] . '%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('invoice_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('invoice_date', '<=', $filters['date_to']);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->get();

        if ($format === 'csv') {
            return $this->exportToCsv($invoices);
        } else {
            return $this->exportToExcel($invoices);
        }
    }

    /**
     * Export to CSV format
     */
    private function exportToCsv($invoices)
    {
        $filename = 'invoices_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/public/exports/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');

        // Write headers
        fputcsv($file, [
            'Invoice Number',
            'Fornitore',
            'Fornitore PIVA',
            'Invoice Date',
            'Total Amount',
            'Tax Amount',
            'Currency',
            'Status',
            'COGE'
        ], ';');

        // Write data
        foreach ($invoices as $invoice) {
            fputcsv($file, [
                $invoice->invoice_number,
                $invoice->fornitore,
                $invoice->fornitore_piva,
                $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '',
                $invoice->total_amount,
                $invoice->tax_amount,
                $invoice->currency,
                $invoice->status,
                $invoice->coge
            ], ';');
        }

        fclose($file);

        return [
            'filepath' => $filepath,
            'filename' => $filename,
            'count' => $invoices->count()
        ];
    }

    /**
     * Export to Excel format
     */
    private function exportToExcel($invoices)
    {
        $filename = 'invoices_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = storage_path('app/public/exports/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        // Use Laravel Excel if available
        if (class_exists('\Maatwebsite\Excel\Facades\Excel')) {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\InvoicesExport($invoices),
                $filename
            );
        }

        // Fallback to CSV if Excel not available
        return $this->exportToCsv($invoices);
    }

    /**
     * Update existing invoices with missing coge values
     */
    public function updateInvoicesWithMissingCoge()
    {
        $updatedCount = 0;
        $errors = [];

        try {
            // Use transaction to ensure data consistency
            DB::transaction(function() use (&$updatedCount, &$errors) {
                // Find invoices with NULL coge that have matching invoiceins
                $invoicesToUpdate = Invoice::whereNull('coge')
                    ->whereExists(function($query) {
                        $query->select(\DB::raw(1))
                              ->from('invoiceins')
                              ->whereRaw('invoiceins.nr_documento = invoices.invoice_number');
                    })
                    ->get();

                Log::info("Found invoices with missing coge values", [
                    'count' => $invoicesToUpdate->count()
                ]);

                foreach ($invoicesToUpdate as $invoice) {
                    try {
                        // Find the matching invoicein
                        $invoicein = \App\Models\Invoicein::where('nr_documento', $invoice->invoice_number)->first();

                        if ($invoicein && $invoicein->nr_cliente_fornitore) {
                            // Update the invoice with the correct coge
                            $oldCoge = $invoice->coge;
                            $newCoge = $invoicein->nr_cliente_fornitore;

                            $invoice->coge = $newCoge;
                            $invoice->fornitore_piva = $invoicein->partita_iva;
                            $invoice->fornitore = $invoicein->nome_fornitore;
                            $invoice->save();

                            $updatedCount++;

                            Log::info("Updated invoice with coge", [
                                'invoice_number' => $invoice->invoice_number,
                                'old_coge' => $oldCoge,
                                'new_coge' => $newCoge
                            ]);
                        } else {
                            Log::warning("No matching invoicein found for invoice", [
                                'invoice_number' => $invoice->invoice_number
                            ]);
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Error updating invoice {$invoice->invoice_number}: " . $e->getMessage();
                        Log::error("Error updating invoice", [
                            'invoice_number' => $invoice->invoice_number,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });

            Log::info("Update completed", [
                'updated' => $updatedCount,
                'errors_count' => count($errors)
            ]);

        } catch (\Exception $e) {
            $errors[] = "Error updating invoices: " . $e->getMessage();
            Log::error("Error in updateInvoicesWithMissingCoge", [
                'error' => $e->getMessage()
            ]);
        }

        return [
            'updated' => $updatedCount,
            'errors' => $errors
        ];
    }

    /**
     * Transfer invoiceins to invoices table using clientis join
     */
    public function transferInvoiceinsToInvoicesByClienti()
    {
        $invoicesImported = 0;
        $invoicesSkipped = 0;
        $errors = [];

        try {
            // Get invoiceins that have matching clientis
            $invoiceins = \App\Models\Invoicein::join('clientis', 'clientis.coge', '=', 'invoiceins.nr_cliente_fornitore')
                ->select('invoiceins.*')
                ->get();

            Log::info("Starting transfer of invoiceins to invoices (by clientis)", [
                'total_eligible_invoiceins' => $invoiceins->count()
            ]);

            foreach ($invoiceins as $invoicein) {
                try {
                    $result = $this->createInvoiceFromInvoicein($invoicein);
                    if ($result['success']) {
                        $invoicesImported++;
                        Log::info("Successfully created invoice from invoicein (by clienti)", [
                            'invoicein_id' => $invoicein->id,
                            'invoice_number' => $invoicein->nr_documento
                        ]);
                    } else {
                        $invoicesSkipped++;
                        Log::info("Skipped invoicein (by clienti)", [
                            'invoicein_id' => $invoicein->id,
                            'invoice_number' => $invoicein->nr_documento,
                            'reason' => $result['reason']
                        ]);
                        if ($result['reason']) {
                            $errors[] = $result['reason'];
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error processing invoicein {$invoicein->nr_documento}: " . $e->getMessage();
                    Log::error("Error processing invoicein (by clienti)", [
                        'invoicein_id' => $invoicein->id,
                        'invoice_number' => $invoicein->nr_documento,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("Transfer (by clientis) completed", [
                'imported' => $invoicesImported,
                'skipped' => $invoicesSkipped,
                'errors_count' => count($errors)
            ]);

        } catch (\Exception $e) {
            $errors[] = "Error importing to invoices (by clientis): " . $e->getMessage();
            Log::error("Error in transferInvoiceinsToInvoicesByClienti", [
                'error' => $e->getMessage()
            ]);
        }

        return [
            'imported' => $invoicesImported,
            'skipped' => $invoicesSkipped,
            'errors' => $errors
        ];
    }
}
