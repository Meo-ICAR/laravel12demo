<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Browsershot\Browsershot;

class CaptureHelpScreenshots extends Command
{
    protected $signature = 'help:capture-screenshots';
    protected $description = 'Capture screenshots for help/manual pages';

    public function handle()
    {
        $pages = [
            'dashboard' => url('/dashboard'),
            'users' => url('/users'),
            'roles' => url('/roles'),
            'permissions' => url('/permissions'),
            'companies' => url('/companies'),
            'provvigioni' => url('/provvigioni'),
            'proforma-summary' => url('/provvigioni/proforma-summary'),
            'invoice-reconciliation' => url('/invoices/reconciliation'),
            'fornitori' => url('/fornitoris'),
            'calls' => url('/calls'),
            'leads' => url('/leads'),
            'clienti' => url('/clientis'),
            // Add more as needed
        ];

        foreach ($pages as $name => $url) {
            $this->info("Capturing $name ($url)...");
            Browsershot::url($url)
                ->setOption('args', ['--no-sandbox']) // Needed for some environments
                ->windowSize(1920, 1080)
                ->waitUntilNetworkIdle()
                ->save(public_path("images/help/{$name}.png"));
        }

        $this->info('All screenshots captured!');
    }
}
