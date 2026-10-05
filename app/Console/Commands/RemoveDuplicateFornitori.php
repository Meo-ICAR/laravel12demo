<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Fornitori;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateFornitori extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fornitori:remove-duplicates {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate fornitori records based on codice field';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No records will be actually deleted');
        }

        $this->info('Finding duplicate fornitori records based on codice...');

        // Find duplicates based on codice
        $duplicates = DB::table('fornitoris')
            ->select('codice', DB::raw('COUNT(*) as count'))
            ->whereNotNull('codice')
            ->where('codice', '!=', '')
            ->groupBy('codice')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate records found based on codice field.');
            return 0;
        }

        $this->info("Found {$duplicates->count()} codice values with duplicates:");

        $totalDuplicates = 0;
        $recordsToDelete = [];

        foreach ($duplicates as $duplicate) {
            $this->line("  - Codice '{$duplicate->codice}': {$duplicate->count} records");
            $totalDuplicates += $duplicate->count - 1; // -1 because we keep one record

            // Get all records with this codice
            $records = Fornitori::where('codice', $duplicate->codice)
                ->orderBy('created_at', 'desc') // Keep the most recent
                ->get();

            // Keep the first (most recent) record, mark others for deletion
            $recordsToKeep = $records->shift(); // Remove and get the first record
            $recordsToDelete = array_merge($recordsToDelete, $records->pluck('id')->toArray());

            $this->line("    Keeping record ID: {$recordsToKeep->id} (created: {$recordsToKeep->created_at})");
            foreach ($records as $record) {
                $this->line("    Will delete record ID: {$record->id} (created: {$record->created_at})");
            }
        }

        $this->newLine();
        $this->info("Total records to delete: {$totalDuplicates}");

        if ($isDryRun) {
            $this->info('Dry run completed. No records were deleted.');
            return 0;
        }

        if (!$this->confirm('Do you want to proceed with deleting these duplicate records?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Delete the duplicate records
        $deletedCount = Fornitori::whereIn('id', $recordsToDelete)->delete();

        $this->info("Successfully deleted {$deletedCount} duplicate records.");

        // Verify the cleanup
        $remainingDuplicates = DB::table('fornitoris')
            ->select('codice', DB::raw('COUNT(*) as count'))
            ->whereNotNull('codice')
            ->where('codice', '!=', '')
            ->groupBy('codice')
            ->having('count', '>', 1)
            ->count();

        if ($remainingDuplicates === 0) {
            $this->info('Verification: No duplicate records remain.');
        } else {
            $this->warn("Verification: {$remainingDuplicates} codice values still have duplicates.");
        }

        return 0;
    }
}
