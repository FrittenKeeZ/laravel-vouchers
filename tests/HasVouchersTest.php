<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Tests\Models\Color;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Vouchers;

/**
 * @internal
 */
class HasVouchersTest extends TestCase
{
    /**
     * Test HasVouchers::createVoucher().
     */
    public function testCreateVoucher(): void
    {
        $user = User::factory()->create();
        $voucher = $user->createVoucher();

        // Check user voucher relation.
        $this->assertTrue($user->is($voucher->owner));
        $this->assertTrue($voucher->is($user->vouchers->first()));
    }

    /**
     * Test HasVouchers::createVoucher() with callback.
     */
    public function testCreateVoucherWithCallback(): void
    {
        $user = User::factory()->create();
        $color = Color::factory()->create();
        $voucher = $user->createVoucher(function (Vouchers $vouchers) use ($color) {
            $vouchers->withEntities($color);
        });

        // Check user voucher relation.
        $this->assertTrue($user->is($voucher->owner));
        $this->assertTrue($voucher->is($user->vouchers->first()));
        $this->assertTrue($color->is($voucher->getEntities(Color::class)->first()));
    }

    /**
     * Test HasVouchers::createVoucher() with associated entities.
     */
    public function testCreateVoucherWithAssociated(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $voucher = $user->createVoucher(function (Vouchers $vouchers) use ($other) {
            $vouchers->withEntities($other);
        });

        // Check user voucher relation.
        $this->assertTrue($user->is($voucher->owner));
        $this->assertTrue($voucher->is($user->vouchers->first()));
        $this->assertFalse($other->is($voucher->owner));
        $this->assertTrue($other->is($voucher->getEntities(User::class)->first()));
        $this->assertTrue($voucher->is($other->voucherEntities->first()->voucher));
        $this->assertTrue($voucher->is($other->associatedVouchers->first()));
    }

    /**
     * Test HasVouchers::createVouchers().
     */
    public function testCreateVouchers(): void
    {
        $user = User::factory()->create();
        $vouchers = $user->createVouchers(3);

        foreach ($vouchers as $index => $voucher) {
            // Check user voucher relation.
            $this->assertTrue($user->is($voucher->owner));
            $this->assertTrue($voucher->is($user->vouchers[$index]));
        }
    }

    /**
     * Test HasVouchers::createVouchers() with callback.
     */
    public function testCreateVouchersWithCallback(): void
    {
        $user = User::factory()->create();
        $color = Color::factory()->create();
        $vouchers = $user->createVouchers(3, function (Vouchers $vouchers) use ($color) {
            $vouchers->withEntities($color);
        });

        foreach ($vouchers as $index => $voucher) {
            // Check user voucher relation.
            $this->assertTrue($user->is($voucher->owner));
            $this->assertTrue($voucher->is($user->vouchers[$index]));
            $this->assertTrue($color->is($voucher->getEntities(Color::class)->first()));
        }
    }
}
