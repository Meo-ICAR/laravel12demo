<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mfcompenso;

class SyncDenominazioniToFornitori extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mfcompensos:sync-denominazioni {--dry-run : Show what would be added without actually adding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync missing denominazione_riferimento from mfcompensos to fornitori table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sync of denominazioni to fornitori...');

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get all unique denominazione_riferimento from mfcompensos
        $denominazioni = Mfcompenso::query()
            ->whereNotNull('denominazione_riferimento')
            ->where('denominazione_riferimento', '!=', '')
            ->distinct()
            ->pluck('denominazione_riferimento');

        $this->info("Found {$denominazioni->count()} unique denominazioni in mfcompensos");

        $added = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($denominazioni->count());
        $progressBar->start();

        foreach ($denominazioni as $denominazione) {
            if (!$denominazione) {
                $progressBar->advance();
                continue;
            }

            // Check if this denominazione already exists in fornitoris
            $exists = \App\Models\Fornitori::where('name', $denominazione)->exists();

            if (!$exists) {
                if (!$this->option('dry-run')) {
                    try {
                        \App\Models\Fornitori::create([
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'name' => $denominazione,
                            'codice' => null,
                            'piva' => null,
                            'email' => null,
                            'operatore' => null,
                            'iscollaboratore' => false,
                            'isdipendente' => false,
                            'regione' => null,
                            'citta' => null,
                            'company_id' => null,
                        ]);
                        $added++;
                    } catch (\Exception $e) {
                        $this->error("Failed to add '$denominazione': " . $e->getMessage());
                        $errors++;
                    }
                } else {
                    $added++;
                }
            } else {
                $skipped++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->info('Sync completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $denominazioni->count()],
                ['Added', $added],
                ['Skipped (already exists)', $skipped],
                ['Errors', $errors],
            ]
        );

        if ($this->option('dry-run')) {
            $this->warn('This was a dry run. Run without --dry-run to actually add the records.');
        }

        return 0;
    }
}
