<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class ImportDailyData extends Command
{
    protected $signature = 'import:daily 
                            {--start-date= : The start date (YYYY-MM-DD)}
                            {--end-date= : The end date (YYYY-MM-DD)}';
    
    protected $description = 'Run all daily import commands with optional date range';

    public function handle()
    {
        $endDate = $this->option('end-date') 
            ? Carbon::parse($this->option('end-date'))->format('Y-m-d')
            : Carbon::yesterday()->format('Y-m-d');
            
        $startDate = $this->option('start-date')
            ? Carbon::parse($this->option('start-date'))->format('Y-m-d')
            : $endDate; // Default to same as end date if not specified

        $commands = [
            [
                'command' => 'sidial:import-leads',
                'params' => [
                    '--fromDay' => Carbon::parse($startDate)->format('d/m/Y'),
                    '--today' => Carbon::parse($endDate)->format('d/m/Y'),
                ]
            ],
            [
                'command' => 'sidial:import-esiti',
                'params' => [
                    '--from' => Carbon::parse($startDate)->format('d/m/Y'),
                    '--to' => Carbon::parse($endDate)->format('d/m/Y'),
                    '--table' => 'calls',
                ]
            ],
            [
                'command' => 'pratiche:import-api',
                'params' => [
                    '--start-date' => $startDate,
                    '--end-date' => $endDate,
                ]
            ],
            /*
            [
                'command' => 'provvigioni:import-api',
                'params' => [
                    '--start-date' => $startDate,
                    '--end-date' => $endDate,
                ]
            ],
            */
        ];

        foreach ($commands as $cmd) {
            $this->info("Running: {$cmd['command']}...");
            $this->call($cmd['command'], $cmd['params']);
            $this->newLine();
        }

        $this->info('All daily imports completed!');
        return 0;
    }
}
