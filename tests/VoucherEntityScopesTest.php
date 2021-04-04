<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Models\VoucherEntity;
use FrittenKeeZ\Vouchers\Tests\Models\Color;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Vouchers;

/**
 * @internal
 */
class VoucherEntityScopesTest extends TestCase
{
    /**
     * Test Voucher::scopeWithEntityType() and Voucher::scopeWithEntity().
     *
     * @return void
     */
    public function testEntityScopes(): void
    {
        $vouchers = new Vouchers();

        // Create user.
        $user = $this->factory(User::class)->create();

        // Create vouchers.
        $first = $vouchers->withEntities(
            $user,
            ...$this->factory(User::class, 2)->create(),
            ...$this->factory(Color::class, 3)->create()
        )->create();
        $second = $vouchers->withEntities(
            ...$this->factory(User::class, 3)->create(),
            ...$this->factory(Color::class, 6)->create()
        )->create();

        $this->assertTrue(VoucherEntity::withEntity($user)->exists());
        $this->assertSame(6, VoucherEntity::withEntityType(User::class)->count());
        $this->assertSame(9, VoucherEntity::withEntityType(Color::class)->count());
        $this->assertSame(3, $first->voucherEntities()->withEntityType(User::class)->count());
        $this->assertSame(3, $first->voucherEntities()->withEntityType(Color::class)->count());
        $this->assertSame(3, $second->voucherEntities()->withEntityType(User::class)->count());
        $this->assertSame(6, $second->voucherEntities()->withEntityType(Color::class)->count());
    }
}
