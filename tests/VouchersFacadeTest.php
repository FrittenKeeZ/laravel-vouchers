<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Facades\Vouchers;
use ReflectionMethod;

uses(FrittenKeeZ\Vouchers\Tests\TestCase::class);

/**
 * @internal
 */

/**
 * Test facade instance through app::make().
 */
test('facade instance', function () {
    $this->assertInstanceOf(Vouchers::class, app()->make('Vouchers'));
});

/**
 * Test facade accessor.
 */
test('facade accessor', function () {
    $method = new ReflectionMethod(Vouchers::class, 'getFacadeAccessor');
    $method->setAccessible(true);

    $this->assertSame('vouchers', $method->invoke(null));
});
