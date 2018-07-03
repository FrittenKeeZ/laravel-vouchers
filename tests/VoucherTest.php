<?php

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Vouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Tests\Models\User;

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
        $user = factory(User::class)->create();
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
        $user = factory(User::class)->create();
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
        $user = factory(User::class)->create();
        $voucher = $vouchers->create();
        $this->assertTrue($voucher->isRedeemable());
        $this->assertTrue($vouchers->redeem($voucher->code, $user));
        $voucher->refresh();
        $this->assertFalse($voucher->isRedeemed());
        $this->assertNotEmpty($voucher->redeemers);
        $this->assertTrue($voucher->redeem($voucher->redeemers->first()));
    }
}
