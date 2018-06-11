<?php

namespace FrittenKeeZ\Vouchers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Configuration path.
     *
     * @var string
     */
    const CONFIG_PATH = __DIR__ . '/../config/vouchers.php';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->publishes([
        //     self::CONFIG_PATH => config_path('vouchers.php'),
        // ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // $this->mergeConfigFrom(
        //     self::CONFIG_PATH,
        //     'vouchers'
        // );

        // $this->app->bind('vouchers', function () {
        //     return new Vouchers();
        // });
    }
}
