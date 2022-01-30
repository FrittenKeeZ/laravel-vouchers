<?php

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Models\Voucher;

/**
 * @internal
 */
class VoucherDbConnectionTest extends TestCase
{
    /**
     * Test if the model can use the database connection defined in the config.
     *
     * @return void
     */
    public function testUsesConnectionFromConfig() : void
    {
        $voucher = new Voucher();
        $this->assertEquals($voucher->getConnectionName(), config('vouchers.db_connection'));
        $this->assertNotNull($voucher->getConnectionName());
    }

    /**
     * Test if the model can use the default database connection if no connection is defined in the config.
     * @return void
     */
    public function testUsesDefaultConnectionIfNotSet() : void
    {
        config()->set('vouchers.db_connection', null);

        $voucher = new Voucher();
        $this->assertInstanceOf('Illuminate\Database\SQLiteConnection', $voucher->getConnection());
        $this->assertNull(config('vouchers.db_connection'));
    }
}
