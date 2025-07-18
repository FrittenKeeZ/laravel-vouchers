<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Vouchers;

/**
 * Test vouchers instance through app::make().
 */
test('instance', function () {
    expect(app()->make('vouchers'))->toBeInstanceOf(Vouchers::class);
});

/**
 * Test that Vouchers::getConfig() returns clone and not same instance.
 */
test('config clone', function () {
    $vouchers = new Vouchers();
    $config = $vouchers->getConfig();

    expect($config)->not->toBe($vouchers->getConfig());
});

/**
 * Test Vouchers::wrap() method.
 */
test('string wrapping', function (string $str, ?string $prefix, ?string $suffix, string $separator, string $expected) {
    expect((new Vouchers())->wrap($str, $prefix, $suffix, $separator))->toBe($expected);
})->with([
    'string only'                        => ['code', null, null, '-', 'code'],
    'prefix dash separator'              => ['code', 'foo', null, '-', 'foo-code'],
    'suffix dash separator'              => ['code', null, 'bar', '-', 'code-bar'],
    'prefix suffix dash separator'       => ['code', 'foo', 'bar', '-', 'foo-code-bar'],
    'prefix suffix underscore separator' => ['code', 'foo', 'bar', '_', 'foo_code_bar'],
]);
