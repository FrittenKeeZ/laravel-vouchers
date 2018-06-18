<?php

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Facades\Vouchers;

class VouchersFacadeTest extends TestCase
{
    /**
     * Test facade instance through app::make().
     *
     * @return void
     */
    public function testFacadeInstance(): void
    {
        $this->assertInstanceOf(Vouchers::class, $this->app->make('Vouchers'));
    }
}
