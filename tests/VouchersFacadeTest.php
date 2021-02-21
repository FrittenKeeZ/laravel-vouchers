<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Facades\Vouchers;
use ReflectionMethod;

/**
 * @internal
 */
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

    /**
     * Test facade accessor.
     *
     * @return void
     */
    public function testFacadeAccessor(): void
    {
        $method = new ReflectionMethod(Vouchers::class, 'getFacadeAccessor');
        $method->setAccessible(true);

        $this->assertSame('vouchers', $method->invoke(null));
    }
}
