<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProvvCoge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'provv:coge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch data from vwcogeprovvigioni and post to COGE_URL';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cogeUrl = env('COGE_URL');

        if (empty($cogeUrl)) {
            $this->error('COGE_URL is not set in .env file');
            return 1;
        }

        $this->info('Fetching data from vwcogeprovvigioni...');

        try {
            $records = DB::select('SELECT * FROM vwcogeprovvigioni');

            if (empty($records)) {
                $this->info('No records found in vwcogeprovvigioni view.');
                return 0;
            }

            $this->info('Found ' . count($records) . ' records. Sending to COGE...');

            $successCount = 0;

            $apiKey = env('COGE_API_KEY');

            if (empty($apiKey)) {
                $this->error('COGE_API_KEY is not set in .env file');
                return 1;
            }

            foreach ($records as $record) {
                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Accept' => 'application/json',
                    ])->post($cogeUrl, (array) $record);

                    if ($response->successful()) {
                        $successCount++;
                    } else {
                        $this->error('Failed to send record: ' . $response->body());
                        Log::error('Failed to send record to COGE', [
                            'record' => $record,
                            'status' => $response->status(),
                            'response' => $response->body()
                        ]);
                    }
                } catch (\Exception $e) {
                    $this->error('Error sending record: ' . $e->getMessage());
                    Log::error('Error sending record to COGE', [
                        'record' => $record,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            $this->info("Successfully sent $successCount out of " . count($records) . " records to COGE.");
            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Error in ProvvCoge command', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
