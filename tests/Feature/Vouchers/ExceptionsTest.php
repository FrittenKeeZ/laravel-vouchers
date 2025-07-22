<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Exceptions;
use FrittenKeeZ\Vouchers\Tests\Models\Redeemer;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Tests\Models\Voucher;
use FrittenKeeZ\Vouchers\Vouchers;

/**
 * Test redeeming voucher not found exception.
 */
test('redeeming voucher not found exception', function () {
    $vouchers = new Vouchers();
    $user = User::factory()->create();

    $vouchers->redeem('idonotexist', $user);
})->throws(Exceptions\VoucherNotFoundException::class);

/**
 * Test redeeming voucher redeemed exception.
 */
test('redeeming voucher redeemed exception', function () {
    $vouchers = new Vouchers();
    $voucher = Voucher::factory()->redeemed()->create();
    $user = User::factory()->create();

    $vouchers->redeem($voucher->code, $user);
})->throws(Exceptions\VoucherRedeemedException::class);

/**
 * Test redeeming voucher unstarted exception.
 */
test('redeeming voucher unstarted exception', function () {
    $vouchers = new Vouchers();
    $voucher = Voucher::factory()->started(false)->create();
    $user = User::factory()->create();

    $vouchers->redeem($voucher->code, $user);
})->throws(Exceptions\VoucherUnstartedException::class);

/**
 * Test redeeming voucher expired exception.
 */
test('redeeming voucher expired exception', function () {
    $vouchers = new Vouchers();
    $voucher = Voucher::factory()->expired()->create();
    $user = User::factory()->create();

    $vouchers->redeem($voucher->code, $user);
})->throws(Exceptions\VoucherExpiredException::class);

/**
 * Test unredeeming voucher not found exception.
 */
test('unredeeming voucher not found exception', function () {
    $vouchers = new Vouchers();
    $user = User::factory()->create();

    $vouchers->unredeem('idonotexist', $user);
})->throws(Exceptions\VoucherNotFoundException::class);

/**
 * Test unredeeming voucher redeemer not found exception.
 */
test('unredeeming voucher redeemer not found exception', function () {
    $vouchers = new Vouchers();
    $voucher = Voucher::factory()->redeemed()->has(Redeemer::factory()->for(User::factory(), 'redeemer'))->create();
    $user = User::factory()->create();

    $vouchers->unredeem($voucher->code, $user);
})->throws(Exceptions\VoucherRedeemerNotFoundException::class);

/**
 * Test unredeeming voucher redeemer not found exception with callback filter.
 */
test('unredeeming voucher redeemer not found exception with callback filter', function () {
    $vouchers = new Vouchers();
    $voucher = Voucher::factory()->redeemed()->create();
    Redeemer::factory()->for(User::factory(), 'redeemer')->for($voucher)->create(['metadata' => ['foo' => 'bar']]);
    Redeemer::factory()->for(User::factory(), 'redeemer')->for($voucher)->create(['metadata' => ['foo' => 'baz']]);

    $vouchers->unredeem($voucher->code, null, fn ($query) => $query->where('metadata->foo', 'nope'));
})->throws(Exceptions\VoucherRedeemerNotFoundException::class);

/**
 * Test unredeeming voucher unstarted exception.
 */
test('unredeeming voucher unstarted exception', function () {
    $vouchers = new Vouchers();
    $user = User::factory()->create();
    $voucher = Voucher::factory()->started(false)->has(Redeemer::factory()->for($user, 'redeemer'))->create();

    $vouchers->unredeem($voucher->code, $user);
})->throws(Exceptions\VoucherUnstartedException::class);

/**
 * Test unredeeming voucher expired exception.
 */
test('unredeeming voucher expired exception', function () {
    $vouchers = new Vouchers();
    $user = User::factory()->create();
    $voucher = Voucher::factory()->expired()->has(Redeemer::factory()->for($user, 'redeemer'))->create();

    $vouchers->unredeem($voucher->code, $user);
})->throws(Exceptions\VoucherExpiredException::class);

/**
 * Test invalid magic call (Vouchers::__call()).
 */
test('invalid magic call', function () {
    $vouchers = new Vouchers();
    $vouchers->methodthatdoesnotexist();
})->throws(ErrorException::class, 'Call to undefined method ' . Vouchers::class . '::methodthatdoesnotexist()');
