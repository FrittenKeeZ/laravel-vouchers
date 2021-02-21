<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Vouchers;

/**
 * @internal
 */
class VoucherTest extends TestCase
{
    /**
     * Test redeeming event.
     *
     * @return void
     */
    public function testRedeemingEvent(): void
    {
        // Prevent redeeming.
        Voucher::redeeming(function (Voucher $voucher) {
            $this->assertFalse($voucher->isRedeemed());

            return false;
        });

        $vouchers = new Vouchers();
        $user = $this->factory(User::class)->create();
        $voucher = $vouchers->create();
        $this->assertTrue($voucher->isRedeemable());
        $this->assertFalse($vouchers->redeem($voucher->code, $user));
        $voucher->refresh();
        $this->assertFalse($voucher->isRedeemed());
        $this->assertEmpty($voucher->redeemers);
    }

    /**
     * Test redeemed event.
     *
     * @return void
     */
    public function testRedeemedEvent(): void
    {
        // Check that voucher is redeemed.
        Voucher::redeemed(function (Voucher $voucher) {
            $this->assertTrue($voucher->isRedeemed());
        });

        $vouchers = new Vouchers();
        $user = $this->factory(User::class)->create();
        $voucher = $vouchers->create();
        $this->assertTrue($voucher->isRedeemable());
        $this->assertTrue($vouchers->redeem($voucher->code, $user));
        $voucher->refresh();
        $this->assertNotEmpty($voucher->redeemers);
        $this->assertFalse($voucher->redeem($voucher->redeemers->first()));
    }

    /**
     * Test shouldMarkRedeemed event.
     *
     * @return void
     */
    public function testShouldMarkRedeemedEvent(): void
    {
        // Don't mark voucher as redeemed for multiple uses.
        Voucher::shouldMarkRedeemed(function (Voucher $voucher) {
            $this->assertFalse($voucher->isRedeemed());

            return false;
        });

        $vouchers = new Vouchers();
        $user = $this->factory(User::class)->create();
        $voucher = $vouchers->create();
        $this->assertTrue($voucher->isRedeemable());
        $this->assertTrue($vouchers->redeem($voucher->code, $user));
        $voucher->refresh();
        $this->assertFalse($voucher->isRedeemed());
        $this->assertNotEmpty($voucher->redeemers);
        $this->assertTrue($voucher->redeem($voucher->redeemers->first()));
    }

    /**
     * Test redeeming by owning user only event.
     *
     * @return void
     */
    public function testRedeemingByOwningUserOnlyEvent(): void
    {
        // Allow redeeming only for related user.
        Voucher::redeeming(function (Voucher $voucher) {
            return $voucher->redeemer->redeemer->is($voucher->owner);
        });

        $vouchers = new Vouchers();
        $user = $this->factory(User::class)->create();
        $other = $this->factory(User::class)->create();
        $voucher = $vouchers->withOwner($user)->create();
        $this->assertTrue($voucher->isRedeemable());
        $this->assertFalse($vouchers->redeem($voucher->code, $other));
        $this->assertFalse($voucher->isRedeemed());
        $this->assertEmpty($voucher->redeemers);
        $voucher->refresh();
        $this->assertTrue($voucher->isRedeemable());
        $this->assertTrue($vouchers->redeem($voucher->code, $user));
        $voucher->refresh();
        $this->assertTrue($voucher->isRedeemed());
        $this->assertNotEmpty($voucher->redeemers);
    }
}
