<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\Tests\Models\Color;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\VouchersServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\App;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * @internal
 */
class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();
        $this->artisan('migrate', ['--database' => 'testing']);
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        if ((float) App::version() < 8) {
            $this->withFactories(__DIR__ . '/database/factories-legacy');
        }

        // Ensure everything works with morph map.
        Relation::morphMap([
            'Color' => Color::class,
            'User'  => User::class,
        ]);
    }

    /**
     * Get package aliases.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app): array
    {
        return ['Vouchers' => Vouchers::class];
    }

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [VouchersServiceProvider::class];
    }

    /**
     * Factory helper to handle both Laravel 8 and earlier versions.
     *
     * @param string   $class
     * @param int|null $amount
     *
     * @return \Illuminate\Database\Eloquent\FactoryBuilder|Illuminate\Database\Eloquent\Factories\Factory
     */
    protected function factory(string $class, ?int $amount = null)
    {
        if (class_exists('Illuminate\\Database\\Eloquent\\Factory')) {
            $factory = App::make('Illuminate\\Database\\Eloquent\\Factory');

            if (isset($amount) && \is_int($amount)) {
                return $factory->of($class)->times($amount);
            }

            return $factory->of($class);
        }

        $factory = 'FrittenKeeZ\\Vouchers\\Tests\\Database\\Factories\\' . class_basename($class) . 'Factory';
        if (isset($amount) && \is_int($amount)) {
            return $factory::new()->count($amount);
        }

        return $factory::new();
    }
}
