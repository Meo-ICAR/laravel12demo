<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\ImportInvoiceins::class,
        \App\Console\Commands\CaptureHelpScreenshots::class,
        \App\Console\Commands\ImportLeadsFromSidialLeads::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * These schedules are run in a single process, so avoid heavy processing.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire:inspire')->hourly();
         // Import esiti from SIDIAL daily at 02:15
        $schedule->command('sidial:import-esiti')->dailyAt('02:15');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
