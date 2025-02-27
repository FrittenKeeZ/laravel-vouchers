<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Models\VoucherEntity;
use FrittenKeeZ\Vouchers\Tests\Models\Color;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Vouchers;

uses(FrittenKeeZ\Vouchers\Tests\TestCase::class);

/**
 * @internal
 */

/**
 * Test Voucher::scopeWithEntityType() and Voucher::scopeWithEntity().
 */
test('entity scopes', function () {
    $vouchers = new Vouchers;

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
