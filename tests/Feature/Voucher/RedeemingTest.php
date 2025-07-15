<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Tests\Models\Redeemer;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Tests\Models\Voucher;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('vouchers.models.redeemer', Redeemer::class);
    Config::set('vouchers.models.voucher', Voucher::class);
});

/**
 * Test repeated redeeming.
 */
test('repeated redeeming', function () {
    $voucher = Voucher::factory()->create();
    $redeemer = Redeemer::factory()->for(User::factory(), 'redeemer')->make();

    expect($voucher->isRedeemable())->toBeTrue();
    expect($voucher->redeem($redeemer))->toBeTrue();
    expect($voucher->isRedeemed())->toBeTrue();
    expect($voucher->redeemers)->not->toBeEmpty();
    expect($voucher->isRedeemable())->toBeFalse();
    expect($voucher->redeem($redeemer))->toBeFalse();
});

/**
 * Test redeeming event.
 */
test('redeeming event', function () {
    // Prevent redeeming.
    Voucher::redeeming(function (Voucher $voucher) {
        expect($voucher->isRedeemed())->toBeFalse();

        return false;
    });

    $voucher = Voucher::factory()->create();
    $redeemer = Redeemer::factory()->for(User::factory(), 'redeemer')->make();

    expect($voucher->isRedeemable())->toBeTrue();
    expect($voucher->redeem($redeemer))->toBeFalse();
    expect($voucher->isRedeemed())->toBeFalse();
    expect($voucher->redeemers)->toBeEmpty();
});

/**
 * Test redeemed event.
 */
test('redeemed event', function () {
    // Check that voucher is redeemed.
    Voucher::redeemed(function (Voucher $voucher) {
        expect($voucher->isRedeemed())->toBeTrue();
    });

    $voucher = Voucher::factory()->create();
    $redeemer = Redeemer::factory()->for(User::factory(), 'redeemer')->make();

    expect($voucher->isRedeemable())->toBeTrue();
    expect($voucher->redeem($redeemer))->toBeTrue();
    expect($voucher->redeemers)->not->toBeEmpty();
    expect($voucher->isRedeemable())->toBeFalse();
});

/**
 * Test shouldMarkRedeemed event.
 */
test('should mark redeemed event', function () {
    // Don't mark voucher as redeemed for multiple uses.
    Voucher::shouldMarkRedeemed(function (Voucher $voucher) {
        expect($voucher->isRedeemed())->toBeFalse();

        return false;
    });

    $voucher = Voucher::factory()->create();
    $redeemer = Redeemer::factory()->for(User::factory(), 'redeemer')->make();

    expect($voucher->isRedeemable())->toBeTrue();
    expect($voucher->redeem($redeemer))->toBeTrue();
    expect($voucher->isRedeemed())->toBeFalse();
    expect($voucher->redeemers)->not->toBeEmpty();
    expect($voucher->isRedeemable())->toBeTrue();
    expect($voucher->redeem($redeemer))->toBeTrue();
});

/**
 * Test redeeming by owning user only event.
 */
test('redeeming by owning user only event', function () {
    // Allow redeeming only for related user.
    Voucher::redeeming(function (Voucher $voucher) {
        return $voucher->redeemer->redeemer->is($voucher->owner);
    });

    $owner = User::factory()->create();
    $voucher = Voucher::factory()->for($owner, 'owner')->create();
    $redeemer = Redeemer::factory()->for($owner, 'redeemer')->make();
    $unknown = Redeemer::factory()->for(User::factory(), 'redeemer')->make();

    expect($voucher->isRedeemable())->toBeTrue();
    expect($voucher->redeem($unknown))->toBeFalse();
    expect($voucher->isRedeemed())->toBeFalse();
    expect($voucher->redeemers)->toBeEmpty();

    expect($voucher->isRedeemable())->toBeTrue();
    expect($voucher->redeem($redeemer))->toBeTrue();
    expect($voucher->isRedeemed())->toBeTrue();
    expect($voucher->redeemers)->not->toBeEmpty();
});
