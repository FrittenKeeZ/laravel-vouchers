<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers;

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
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([$this->getPublishConfigPath() => config_path('vouchers.php')], 'config');
        $this->publishes([$this->getPublishMigrationsPath() => database_path('migrations')], 'migrations');

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
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
     *
     * @return string
     */
    protected function getPublishConfigPath(): string
    {
        return __DIR__ . '/../publishes/config/vouchers.php';
    }

    /**
     * Get publish migrations path.
     *
     * @return string
     */
    protected function getPublishMigrationsPath(): string
    {
        return __DIR__ . '/../publishes/migrations/';
    }
}
