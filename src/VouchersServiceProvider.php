<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers;

use FrittenKeeZ\Vouchers\Console\Commands\MigrateCommand;
use Illuminate\Support\ServiceProvider;

class VouchersServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([$this->getPublishConfigPath() => config_path('vouchers.php')], 'config');
        $this->publishes([$this->getPublishMigrationsPath() => database_path('migrations')], 'migrations');

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([MigrateCommand::class]);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom($this->getPublishConfigPath(), 'vouchers');

        $this->app->bind('vouchers', function () {
            return new Vouchers();
        });
    }

    /**
     * Get publish config path.
     */
    protected function getPublishConfigPath(): string
    {
        return __DIR__ . '/../publishes/config/vouchers.php';
    }

    /**
     * Get publish migrations path.
     */
    protected function getPublishMigrationsPath(): string
    {
        return __DIR__ . '/../publishes/migrations/';
    }
}
