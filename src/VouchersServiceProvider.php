<?php

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
    public function boot()
    {
        $this->publishes([$this->getConfigPath() => config_path('vouchers.php')]);

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'vouchers');

        $this->app->bind('vouchers', function () {
            return new Vouchers();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['vouchers'];
    }

    /**
     * Get config path.
     *
     * @return string
     */
    protected function getConfigPath()
    {
        return __DIR__ . '/../config/vouchers.php';
    }
}
