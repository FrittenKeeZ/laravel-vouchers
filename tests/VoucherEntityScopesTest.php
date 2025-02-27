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

    $this->assertTrue(VoucherEntity::withEntity($user)->exists());
    $this->assertSame(6, VoucherEntity::withEntityType(User::class)->count());
    $this->assertSame(9, VoucherEntity::withEntityType(Color::class)->count());
    $this->assertSame(3, $first->voucherEntities()->withEntityType(User::class)->count());
    $this->assertSame(3, $first->voucherEntities()->withEntityType(Color::class)->count());
    $this->assertSame(3, $second->voucherEntities()->withEntityType(User::class)->count());
    $this->assertSame(6, $second->voucherEntities()->withEntityType(Color::class)->count());
});
