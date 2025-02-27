<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Facades\Vouchers;

uses(FrittenKeeZ\Vouchers\Tests\TestCase::class);

/**
 * @internal
 */

/**
 * Test facade instance through app::make().
 */
test('facade instance', function () {
    expect(app()->make('Vouchers'))->toBeInstanceOf(Vouchers::class);
});

/**
 * Test facade accessor.
 */
test('facade accessor', function () {
    $method = new ReflectionMethod(Vouchers::class, 'getFacadeAccessor');
    $method->setAccessible(true);

    expect($method->invoke(null))->toBe('vouchers');
});
