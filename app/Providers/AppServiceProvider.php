<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Livewire\Livewire;
use App\Filament\Pages\CaptureScreenshots;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Permission::class, \App\Models\Permission::class);
        $this->app->bind(Role::class, \App\Models\Role::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Configure Fattura Elettronica package
        $this->configureFatturaElettronica();
    }

    /**
     * Configure Fattura Elettronica package settings
     */
    protected function configureFatturaElettronica(): void
    {
        // Set default timezone for date handling
        if (!ini_get('date.timezone')) {
            ini_set('date.timezone', 'Europe/Rome');
        }

        // Configure XML parsing settings
        libxml_use_internal_errors(true);

        // Set memory limit for large XML files
        ini_set('memory_limit', '512M');
    }
}
