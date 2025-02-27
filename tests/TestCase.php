<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\Tests\Models\Color;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\VouchersServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Orchestra\Testbench\TestCase as BaseTestCase;

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
        $this->loadMigrationsFrom(__DIR__ . '/../publishes/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

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
     */
    protected function getPackageAliases($app): array
    {
        return ['Vouchers' => Vouchers::class];
    }

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getPackageProviders($app): array
    {
        return [VouchersServiceProvider::class];
    }
}
