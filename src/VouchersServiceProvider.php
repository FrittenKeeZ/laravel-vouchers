<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers;

use Illuminate\Support\ServiceProvider;

class VouchersServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([$this->getPublishConfigPath() => config_path('vouchers.php')], 'config');

        $method = method_exists($this, 'publishesMigrations') ? 'publishesMigrations' : 'publishes';
        $this->{$method}([$this->getPublishMigrationsPath() => database_path('migrations')], 'migrations');
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom($this->getPublishConfigPath(), 'vouchers');

        $this->app->bind('vouchers', fn () => new Vouchers());
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
