<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Models\VoucherEntity;
use FrittenKeeZ\Vouchers\Tests\Models\Color;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Vouchers;

/**
 * Test Voucher::scopeWithEntityType() and Voucher::scopeWithEntity().
 */
test('entity scopes', function () {
    $vouchers = new Vouchers();

    // Create user.
    $user = User::factory()->create();

    // Create vouchers.
    $first = $vouchers
        ->withEntities($user, ...User::factory()->count(2)->create(), ...Color::factory()->count(3)->create())
        ->create()
    ;
    $second = $vouchers
        ->withEntities(...User::factory()->count(3)->create(), ...Color::factory()->count(6)->create())
        ->create()
    ;

    expect(VoucherEntity::withEntity($user)->exists())->toBeTrue();
    expect(VoucherEntity::withEntityType(User::class)->count())->toBe(6);
    expect(VoucherEntity::withEntityType(Color::class)->count())->toBe(9);
    expect($first->voucherEntities()->withEntityType(User::class)->count())->toBe(3);
    expect($first->voucherEntities()->withEntityType(Color::class)->count())->toBe(3);
    expect($second->voucherEntities()->withEntityType(User::class)->count())->toBe(3);
    expect($second->voucherEntities()->withEntityType(Color::class)->count())->toBe(6);
});

/**
 * Test Voucher::scopeWithEntityType() accepts a morph map alias, not just a class name.
 *
 * @see \FrittenKeeZ\Vouchers\Tests\TestCase::setUp() for the registered morph map.
 */
test('entity type scope resolves morph alias', function () {
    $vouchers = new Vouchers();

    // Create vouchers with a mix of entity types.
    $vouchers
        ->withEntities(...User::factory()->count(2)->create(), ...Color::factory()->count(3)->create())
        ->create()
    ;

    // Passing the alias must yield the same result as passing the class name.
    expect(VoucherEntity::withEntityType('User')->count())
        ->toBe(VoucherEntity::withEntityType(User::class)->count())
        ->toBe(2)
    ;
    expect(VoucherEntity::withEntityType('Color')->count())
        ->toBe(VoucherEntity::withEntityType(Color::class)->count())
        ->toBe(3)
    ;
});
