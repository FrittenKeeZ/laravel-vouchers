<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Exceptions;
use FrittenKeeZ\Vouchers\Models\Voucher as BaseVoucher;
use FrittenKeeZ\Vouchers\Tests\Models\Redeemer;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Tests\Models\Voucher;
use FrittenKeeZ\Vouchers\Vouchers;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Config::set('vouchers.models.redeemer', Redeemer::class);
    Config::set('vouchers.models.voucher', Voucher::class);
});

/**
 * Test redeeming with a voucher instance instead of a code.
 */
test('redeeming with voucher instance', function () {
    $vouchers = new Vouchers();
    $user = User::factory()->create();
    $voucher = $vouchers->create();

    expect($vouchers->redeemable($voucher))->toBeTrue();

    $metadata = ['foo' => 'bar'];
    expect($vouchers->redeem($voucher, $user, $metadata))->toBeTrue();

    $voucher->refresh();
    expect($voucher->isRedeemed())->toBeTrue();
    expect($vouchers->redeemable($voucher))->toBeFalse();

    $redeemer = $voucher->redeemers->first();
    expect($user->is($redeemer->redeemer))->toBeTrue();
    expect($redeemer->metadata->toArray())->toBe($metadata);
});

/**
 * Test unredeeming with a voucher instance instead of a code.
 */
test('unredeeming with voucher instance', function () {
    $vouchers = new Vouchers();
    $user = User::factory()->create();
    $voucher = Voucher::factory()->redeemed()->has(Redeemer::factory()->for($user, 'redeemer'))->create();

    expect($vouchers->unredeemable($voucher))->toBeTrue();
    expect($vouchers->unredeem($voucher, $user))->toBeTrue();

    $voucher->refresh();
    expect($voucher->isUnredeemable())->toBeFalse();
    expect($vouchers->unredeemable($voucher))->toBeFalse();
});

/**
 * Test that passing an instance yields the same result as passing its code.
 */
test('voucher instance and code are equivalent', function () {
    $vouchers = new Vouchers();
    $voucher = $vouchers->create();

    expect($vouchers->redeemable($voucher))->toBe($vouchers->redeemable($voucher->code));
    expect($vouchers->unredeemable($voucher))->toBe($vouchers->unredeemable($voucher->code));
});

/**
 * Test that passing an instance skips the code lookup query.
 */
test('voucher instance skips lookup query', function () {
    $vouchers = new Vouchers();
    $voucher = $vouchers->create();

    // Count the voucher lookups performed for each path.
    $lookups = static function (callable $callback) use ($voucher): int {
        $count = 0;
        DB::listen(function ($query) use (&$count, $voucher) {
            if (str_contains($query->sql, $voucher->getTable()) && str_contains($query->sql, 'code')) {
                $count++;
            }
        });
        $callback();

        return $count;
    };

    expect($lookups(fn () => $vouchers->redeemable($voucher->code)))->toBe(1);
    expect($lookups(fn () => $vouchers->redeemable($voucher)))->toBe(0);
});

/**
 * Test that an unsaved voucher instance is treated as not found.
 */
test('unsaved voucher instance is not found', function () {
    $vouchers = new Vouchers();
    $voucher = new Voucher(['code' => 'NOT-SAVED']);

    expect($voucher->exists)->toBeFalse();
    expect($vouchers->redeemable($voucher))->toBeFalse();
    expect($vouchers->unredeemable($voucher))->toBeFalse();
});

/**
 * Test redeeming an unsaved voucher instance throws not found.
 */
test('redeeming unsaved voucher instance throws not found', function () {
    $vouchers = new Vouchers();

    $vouchers->redeem(new Voucher(['code' => 'NOT-SAVED']), User::factory()->create());
})->throws(Exceptions\VoucherNotFoundException::class);

/**
 * Test unredeeming an unsaved voucher instance throws not found.
 */
test('unredeeming unsaved voucher instance throws not found', function () {
    $vouchers = new Vouchers();

    $vouchers->unredeem(new Voucher(['code' => 'NOT-SAVED']));
})->throws(Exceptions\VoucherNotFoundException::class);

/**
 * Test that state exceptions still apply when passing an instance.
 */
test('voucher instance still throws state exceptions', function () {
    $vouchers = new Vouchers();
    $user = User::factory()->create();

    expect(fn () => $vouchers->redeem(Voucher::factory()->redeemed()->create(), $user))
        ->toThrow(Exceptions\VoucherRedeemedException::class)
    ;
    expect(fn () => $vouchers->redeem(Voucher::factory()->started(false)->create(), $user))
        ->toThrow(Exceptions\VoucherUnstartedException::class)
    ;
    expect(fn () => $vouchers->redeem(Voucher::factory()->expired()->create(), $user))
        ->toThrow(Exceptions\VoucherExpiredException::class)
    ;
});

/**
 * Test that the base voucher model is accepted, not just the configured one.
 */
test('base voucher instance is accepted', function () {
    $vouchers = new Vouchers();
    $user = User::factory()->create();
    $voucher = BaseVoucher::query()->find($vouchers->create()->getKey());

    expect($voucher)->toBeInstanceOf(BaseVoucher::class);
    expect($vouchers->redeemable($voucher))->toBeTrue();
    expect($vouchers->redeem($voucher, $user))->toBeTrue();
});
