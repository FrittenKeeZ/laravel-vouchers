<?php

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\VouchersServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testing']);
    }

    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return ['Vouchers' => Vouchers::class];
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [VouchersServiceProvider::class];
    }
}