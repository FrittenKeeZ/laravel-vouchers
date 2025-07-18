<?php

declare(strict_types=1);

use Carbon\Carbon;
use FrittenKeeZ\Vouchers\Exceptions;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Vouchers;

/**
 * Test voucher not found exception.
 */
test('voucher not found exception', function () {
    $vouchers = new Vouchers();
    $user = User::factory()->create();

    $vouchers->redeem('idonotexist', $user);
})->throws(Exceptions\VoucherNotFoundException::class);

/**
 * Test voucher redeemed exception.
 */
test('voucher redeemed exception', function () {
    $vouchers = new Vouchers();
    $voucher = $vouchers->create();
    $user = User::factory()->create();

    expect($vouchers->redeem($voucher->code, $user))->toBeTrue();
    $vouchers->redeem($voucher->code, $user);
})->throws(Exceptions\VoucherRedeemedException::class);

/**
 * Test voucher unstarted exception.
 */
test('voucher unstarted exception', function () {
    $vouchers = new Vouchers();
    $voucher = $vouchers->withStartTime(Carbon::now()->addMonth())->create();
    $user = User::factory()->create();

    $vouchers->redeem($voucher->code, $user);
})->throws(Exceptions\VoucherUnstartedException::class);

/**
 * Test voucher expired exception.
 */
test('voucher expired exception', function () {
    $vouchers = new Vouchers();
    $voucher = $vouchers->withExpireTime(Carbon::now()->subMonth())->create();
    $user = User::factory()->create();

    $vouchers->redeem($voucher->code, $user);
})->throws(Exceptions\VoucherExpiredException::class);

/**
 * Test invalid magic call (Vouchers::__call()).
 */
test('invalid magic call', function () {
    $vouchers = new Vouchers();
    $vouchers->methodthatdoesnotexist();
})->throws(ErrorException::class, 'Call to undefined method ' . Vouchers::class . '::methodthatdoesnotexist()');
