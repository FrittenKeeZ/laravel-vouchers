<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Tests\Models\Redeemer;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Tests\Models\Voucher;
use FrittenKeeZ\Vouchers\Vouchers;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('vouchers.models.redeemer', Redeemer::class);
    Config::set('vouchers.models.voucher', Voucher::class);
});

/**
 * Test voucher unredeeming single with entity.
 */
test('voucher unredeeming single with entity', function () {
    $vouchers = new Vouchers();
    $user = User::factory()->create();
    $voucher = Voucher::factory()->redeemed()->has(Redeemer::factory()->for($user, 'redeemer'))->create();

    expect($voucher->isUnredeemable())->toBeTrue();
    expect($vouchers->unredeemable($voucher->code))->toBeTrue();
    expect($vouchers->unredeem($voucher->code, $user))->toBeTrue();

    $voucher->refresh();
    expect($voucher->isUnredeemable())->toBeFalse();
});

/**
 * Test voucher unredeeming single without entity.
 */
test('voucher unredeeming single without entity', function () {
    $vouchers = new Vouchers();
    $voucher = Voucher::factory()->redeemed()->has(Redeemer::factory()->for(User::factory(), 'redeemer'))->create();

    expect($voucher->isUnredeemable())->toBeTrue();
    expect($vouchers->unredeemable($voucher->code))->toBeTrue();
    expect($vouchers->unredeem($voucher->code))->toBeTrue();

    $voucher->refresh();
    expect($voucher->isUnredeemable())->toBeFalse();
});

/**
 * Test voucher unredeeming single with query filter.
 */
test('voucher unredeeming single with query filter', function () {
    Voucher::unredeeming(function (Voucher $voucher) {
        return $voucher->redeemer->metadata['foo'] === 'bar';
    });

    $vouchers = new Vouchers();
    $voucher = Voucher::factory()->create();
    Redeemer::factory()->for(User::factory(), 'redeemer')->for($voucher)->create(['metadata' => ['foo' => 'bar']]);
    Redeemer::factory()->for(User::factory(), 'redeemer')->for($voucher)->create(['metadata' => ['foo' => 'baz']]);

    expect($voucher->isUnredeemable())->toBeTrue();
    expect($voucher->redeemers)->toHaveCount(2);
    expect($vouchers->unredeemable($voucher->code))->toBeTrue();
    expect($vouchers->unredeem($voucher->code, null, fn ($query) => $query->where('metadata->foo', 'bar')))->toBeTrue();

    $voucher->refresh();
    expect($voucher->isUnredeemable())->toBeTrue();
    expect($voucher->redeemers)->toHaveCount(1);
    expect($vouchers->unredeemable($voucher->code))->toBeTrue();
    expect($vouchers->unredeem($voucher->code))->toBeFalse();
});
