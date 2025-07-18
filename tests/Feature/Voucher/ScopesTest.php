<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\Tests\Models\Color;
use FrittenKeeZ\Vouchers\Tests\Models\Redeemer;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Tests\Models\Voucher;

/**
 * Test Voucher::scopeCode().
 */
test('code scope', function () {
    $voucher = Vouchers::create();

    expect(Voucher::code($voucher->code)->exists())->toBeTrue();
    expect(Voucher::code('NOPE')->exists())->toBeFalse();
});

/**
 * Test Voucher::scopeHasPrefix() and Voucher::scopeHasSuffix().
 */
test('prefix and suffix scopes', function () {
    Voucher::create(['code' => 'FOOTESTBAR']);
    Voucher::create(['code' => 'FOOTESTBAZ']);
    Voucher::create(['code' => 'FUUTESTBAR']);
    Voucher::create(['code' => 'FUUTESTBAZ']);
    Voucher::create(['code' => 'FOO-TEST-BAR']);
    Voucher::create(['code' => 'FOO-TEST-BAZ']);
    Voucher::create(['code' => 'FUU-TEST-BAR']);
    Voucher::create(['code' => 'FUU-TEST-BAZ']);

    // Test prefix scope with separator.
    expect(Voucher::withPrefix('FOO')->count())->toBe(2);
    expect(Voucher::withPrefix('FOO', '-')->count())->toBe(2);
    expect(Voucher::withPrefix('FUU')->count())->toBe(2);
    expect(Voucher::withPrefix('FUU', '-')->count())->toBe(2);
    expect(Voucher::withoutPrefix('FOO')->count())->toBe(6);
    expect(Voucher::withoutPrefix('FOO', '-')->count())->toBe(6);
    expect(Voucher::withoutPrefix('FUU')->count())->toBe(6);
    expect(Voucher::withoutPrefix('FUU', '-')->count())->toBe(6);
    // Test prefix scope without separator
    expect(Voucher::withPrefix('FOO', '')->count())->toBe(4);
    expect(Voucher::withPrefix('FUU', '')->count())->toBe(4);
    expect(Voucher::withoutPrefix('FOO', '')->count())->toBe(4);
    expect(Voucher::withoutPrefix('FUU', '')->count())->toBe(4);

    // Test suffix scope with separator.
    expect(Voucher::withSuffix('BAR')->count())->toBe(2);
    expect(Voucher::withSuffix('BAR', '-')->count())->toBe(2);
    expect(Voucher::withSuffix('BAZ')->count())->toBe(2);
    expect(Voucher::withSuffix('BAZ', '-')->count())->toBe(2);
    expect(Voucher::withoutSuffix('BAR')->count())->toBe(6);
    expect(Voucher::withoutSuffix('BAR', '-')->count())->toBe(6);
    expect(Voucher::withoutSuffix('BAZ')->count())->toBe(6);
    expect(Voucher::withoutSuffix('BAZ', '-')->count())->toBe(6);
    // Test suffix scope without separator
    expect(Voucher::withSuffix('BAR', '')->count())->toBe(4);
    expect(Voucher::withSuffix('BAZ', '')->count())->toBe(4);
    expect(Voucher::withoutSuffix('BAR', '')->count())->toBe(4);
    expect(Voucher::withoutSuffix('BAZ', '')->count())->toBe(4);

    // Test prefix and suffix scopes together with separator.
    expect(Voucher::withPrefix('FOO')->withSuffix('BAR')->count())->toBe(1);
    expect(Voucher::withPrefix('FOO', '-')->withSuffix('BAR', '-')->count())->toBe(1);
    expect(Voucher::withPrefix('FUU')->withSuffix('BAR')->count())->toBe(1);
    expect(Voucher::withPrefix('FUU', '-')->withSuffix('BAR', '-')->count())->toBe(1);
    expect(Voucher::withPrefix('FOO')->withSuffix('BAZ')->count())->toBe(1);
    expect(Voucher::withPrefix('FOO', '-')->withSuffix('BAZ', '-')->count())->toBe(1);
    expect(Voucher::withPrefix('FUU')->withSuffix('BAZ')->count())->toBe(1);
    expect(Voucher::withPrefix('FUU', '-')->withSuffix('BAZ', '-')->count())->toBe(1);
    expect(Voucher::withoutPrefix('FOO')->withSuffix('BAR')->count())->toBe(1);
    expect(Voucher::withoutPrefix('FOO', '-')->withSuffix('BAR', '-')->count())->toBe(1);
    expect(Voucher::withoutPrefix('FUU')->withSuffix('BAR')->count())->toBe(1);
    expect(Voucher::withoutPrefix('FUU', '-')->withSuffix('BAR', '-')->count())->toBe(1);
    expect(Voucher::withoutPrefix('FOO')->withSuffix('BAZ')->count())->toBe(1);
    expect(Voucher::withoutPrefix('FOO', '-')->withSuffix('BAZ', '-')->count())->toBe(1);
    expect(Voucher::withoutPrefix('FUU')->withSuffix('BAZ')->count())->toBe(1);
    expect(Voucher::withoutPrefix('FUU', '-')->withSuffix('BAZ', '-')->count())->toBe(1);
    expect(Voucher::withPrefix('FOO')->withoutSuffix('BAR')->count())->toBe(1);
    expect(Voucher::withPrefix('FOO', '-')->withoutSuffix('BAR', '-')->count())->toBe(1);
    expect(Voucher::withPrefix('FUU')->withoutSuffix('BAR')->count())->toBe(1);
    expect(Voucher::withPrefix('FUU', '-')->withoutSuffix('BAR', '-')->count())->toBe(1);
    expect(Voucher::withPrefix('FOO')->withoutSuffix('BAZ')->count())->toBe(1);
    expect(Voucher::withPrefix('FOO', '-')->withoutSuffix('BAZ', '-')->count())->toBe(1);
    expect(Voucher::withPrefix('FUU')->withoutSuffix('BAZ')->count())->toBe(1);
    expect(Voucher::withPrefix('FUU', '-')->withoutSuffix('BAZ', '-')->count())->toBe(1);
    expect(Voucher::withoutPrefix('FOO')->withoutSuffix('BAR')->count())->toBe(5);
    expect(Voucher::withoutPrefix('FOO', '-')->withoutSuffix('BAR', '-')->count())->toBe(5);
    expect(Voucher::withoutPrefix('FUU')->withoutSuffix('BAR')->count())->toBe(5);
    expect(Voucher::withoutPrefix('FUU', '-')->withoutSuffix('BAR', '-')->count())->toBe(5);
    expect(Voucher::withoutPrefix('FOO')->withoutSuffix('BAZ')->count())->toBe(5);
    expect(Voucher::withoutPrefix('FOO', '-')->withoutSuffix('BAZ', '-')->count())->toBe(5);
    expect(Voucher::withoutPrefix('FUU')->withoutSuffix('BAZ')->count())->toBe(5);
    expect(Voucher::withoutPrefix('FUU', '-')->withoutSuffix('BAZ', '-')->count())->toBe(5);
    // Test prefix and suffix scopes together without separator
    expect(Voucher::withPrefix('FOO', '')->withSuffix('BAR', '')->count())->toBe(2);
    expect(Voucher::withPrefix('FUU', '')->withSuffix('BAR', '')->count())->toBe(2);
    expect(Voucher::withPrefix('FOO', '')->withSuffix('BAZ', '')->count())->toBe(2);
    expect(Voucher::withPrefix('FUU', '')->withSuffix('BAZ', '')->count())->toBe(2);
    expect(Voucher::withoutPrefix('FOO', '')->withSuffix('BAR', '')->count())->toBe(2);
    expect(Voucher::withoutPrefix('FUU', '')->withSuffix('BAR', '')->count())->toBe(2);
    expect(Voucher::withoutPrefix('FOO', '')->withSuffix('BAZ', '')->count())->toBe(2);
    expect(Voucher::withoutPrefix('FUU', '')->withSuffix('BAZ', '')->count())->toBe(2);
    expect(Voucher::withPrefix('FOO', '')->withoutSuffix('BAR', '')->count())->toBe(2);
    expect(Voucher::withPrefix('FUU', '')->withoutSuffix('BAR', '')->count())->toBe(2);
    expect(Voucher::withPrefix('FOO', '')->withoutSuffix('BAZ', '')->count())->toBe(2);
    expect(Voucher::withPrefix('FUU', '')->withoutSuffix('BAZ', '')->count())->toBe(2);
    expect(Voucher::withoutPrefix('FOO', '')->withoutSuffix('BAR', '')->count())->toBe(2);
    expect(Voucher::withoutPrefix('FUU', '')->withoutSuffix('BAR', '')->count())->toBe(2);
    expect(Voucher::withoutPrefix('FOO', '')->withoutSuffix('BAZ', '')->count())->toBe(2);
    expect(Voucher::withoutPrefix('FUU', '')->withoutSuffix('BAZ', '')->count())->toBe(2);
});

/**
 * Test Voucher::scopeWithStarted().
 */
test('started scope', function () {
    Voucher::factory()->create();
    Voucher::factory()->started()->create();
    Voucher::factory()->started(false)->create();

    expect(Voucher::count())->toBe(3);
    expect(Voucher::withStarted()->count())->toBe(2);
    expect(Voucher::withoutStarted()->count())->toBe(1);
});

/**
 * Test Voucher::scopeWithExpired().
 */
test('expired scope', function () {
    Voucher::factory()->create();
    Voucher::factory()->expired()->create();
    Voucher::factory()->expired(false)->create();

    expect(Voucher::count())->toBe(3);
    expect(Voucher::withExpired()->count())->toBe(1);
    expect(Voucher::withoutExpired()->count())->toBe(2);
});

/**
 * Test Voucher::scopeWithRedeemed().
 */
test('redeemed scope', function () {
    Voucher::factory()->create();
    Voucher::factory()->redeemed()->create();

    expect(Voucher::count())->toBe(2);
    expect(Voucher::withRedeemed()->count())->toBe(1);
    expect(Voucher::withoutRedeemed()->count())->toBe(1);
});

/**
 * Test Voucher::scopeWithRedeemable().
 */
test('redeemable scope', function () {
    Voucher::factory()->create();
    Voucher::factory()->started()->create();
    Voucher::factory()->started(false)->create();
    Voucher::factory()->expired()->create();
    Voucher::factory()->expired(false)->create();
    Voucher::factory()->redeemed()->create();
    Voucher::factory()->has(Redeemer::factory()->for(User::factory(), 'redeemer'))->create();

    expect(Voucher::count())->toBe(7);
    expect(Voucher::withRedeemable()->count())->toBe(4);
    expect(Voucher::withoutRedeemable()->count())->toBe(3);
});

/**
 * Test Voucher::scopeWithUnredeemable().
 */
test('unredeemable scope', function () {
    Voucher::factory()->create();
    Voucher::factory()->started()->create();
    Voucher::factory()->started(false)->create();
    Voucher::factory()->expired()->create();
    Voucher::factory()->expired(false)->create();
    Voucher::factory()->redeemed()->create();
    Voucher::factory()->has(Redeemer::factory()->for(User::factory(), 'redeemer'))->create();

    expect(Voucher::count())->toBe(7);
    expect(Voucher::withUnredeemable()->count())->toBe(2);
    expect(Voucher::withoutUnredeemable()->count())->toBe(5);
});

/**
 * Test Voucher::scopeWithEntities().
 */
test('entities scope', function () {
    Vouchers::create();
    Vouchers::withEntities(...Color::factory()->count(3)->create())->create();
    Vouchers::withEntities(...User::factory()->count(3)->create())->create();
    Vouchers::withEntities(...Color::factory()->count(3)->create(), ...User::factory()->count(3)->create())->create();

    expect(Voucher::count())->toBe(4);
    expect(Voucher::withEntities()->count())->toBe(3);
    expect(Voucher::withEntities(Color::class)->count())->toBe(2);
    expect(Voucher::withEntities(User::class)->count())->toBe(2);
});

/**
 * Test Voucher::scopeWithOwnerType() and Voucher::scopeWithOwner().
 */
test('owner scopes', function () {
    // Create users.
    $first = User::factory()->create();
    $second = User::factory()->create();
    $third = User::factory()->create();

    // Create vouchers.
    Vouchers::create(2);
    $first->createVoucher();
    $second->createVouchers(2);
    $third->createVouchers(3);

    expect(Voucher::count())->toBe(8);
    expect(Voucher::withoutOwner()->count())->toBe(2);
    expect(Voucher::withOwnerType(User::class)->count())->toBe(6);
    expect(Voucher::withOwner($first)->count())->toBe(1);
    expect(Voucher::withOwner($second)->count())->toBe(2);
    expect(Voucher::withOwner($third)->count())->toBe(3);
});
