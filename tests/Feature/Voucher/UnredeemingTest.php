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
 * Test repeated unredeeming.
 */
test('repeated unredeeming', function () {
    $voucher = Voucher::factory()->redeemed()->create();
    $redeemer = Redeemer::factory()->for(User::factory(), 'redeemer')->for($voucher)->create();

    expect($voucher->isRedeemed())->toBeTrue();
    expect($voucher->redeemers)->not->toBeEmpty();
    expect($voucher->isUnredeemable())->toBeTrue();
    expect($voucher->unredeem($redeemer))->toBeTrue();
    expect($voucher->isRedeemed())->toBeFalse();
    expect($voucher->redeemers)->toBeEmpty();
    expect($voucher->unredeem($redeemer))->toBeFalse();
});

/**
 * Test unredeeming event.
 */
test('unredeeming event', function () {
    // Prevent unredeeming.
    Voucher::unredeeming(function (Voucher $voucher) {
        expect($voucher->isRedeemed())->toBeTrue();

        return false;
    });

    $voucher = Voucher::factory()->redeemed()->create();
    $redeemer = Redeemer::factory()->for(User::factory(), 'redeemer')->for($voucher)->create();

    expect($voucher->isRedeemed())->toBeTrue();
    expect($voucher->redeemers)->not->toBeEmpty();
    expect($voucher->isUnredeemable())->toBeTrue();
    expect($voucher->unredeem($redeemer))->toBeFalse();
    expect($voucher->isRedeemed())->toBeTrue();
    expect($voucher->redeemers)->not->toBeEmpty();
});

/**
 * Test unredeemed event.
 */
test('unredeemed event', function () {
    // Check that voucher is unredeemed.
    Voucher::unredeemed(function (Voucher $voucher) {
        expect($voucher->isRedeemed())->toBeFalse();
    });

    $voucher = Voucher::factory()->redeemed()->create();
    $redeemer = Redeemer::factory()->for(User::factory(), 'redeemer')->for($voucher)->create();

    expect($voucher->isRedeemed())->toBeTrue();
    expect($voucher->redeemers)->not->toBeEmpty();
    expect($voucher->isUnredeemable())->toBeTrue();
    expect($voucher->unredeem($redeemer))->toBeTrue();
    expect($voucher->isRedeemed())->toBeFalse();
    expect($voucher->redeemers)->toBeEmpty();
});

/**
 * Test shouldMarkUnredeemed event.
 */
test('should mark unredeemed event', function () {
    // Don't mark voucher as unredeemed for multiple uses.
    Voucher::shouldMarkUnredeemed(function (Voucher $voucher) {
        expect($voucher->isRedeemed())->toBeTrue();

        return false;
    });

    $voucher = Voucher::factory()->redeemed()->create();
    $primary = Redeemer::factory()->for(User::factory(), 'redeemer')->for($voucher)->create();
    $secondary = Redeemer::factory()->for(User::factory(), 'redeemer')->for($voucher)->create();

    expect($voucher->isRedeemed())->toBeTrue();
    expect($voucher->redeemers)->not->toBeEmpty();
    expect($voucher->isUnredeemable())->toBeTrue();
    expect($voucher->unredeem($primary))->toBeTrue();
    expect($voucher->isRedeemed())->toBeTrue();
    expect($voucher->redeemers)->not->toBeEmpty();
    expect($voucher->unredeem($secondary))->toBeTrue();
    expect($voucher->isRedeemed())->toBeFalse();
    expect($voucher->redeemers)->toBeEmpty();
});

/**
 * Test unredeeming by owning user only event.
 */
test('unredeeming by owning user only event', function () {
    // Allow unredeeming only for related user.
    Voucher::unredeeming(function (Voucher $voucher) {
        return $voucher->redeemer->redeemer->is($voucher->owner);
    });

    $owner = User::factory()->create();
    $voucher = Voucher::factory()->for($owner, 'owner')->redeemed()->create();
    $redeemer = Redeemer::factory()->for($owner, 'redeemer')->for($voucher)->create();
    $unknown = Redeemer::factory()->for(User::factory(), 'redeemer')->for(Voucher::factory()->redeemed())->create();

    expect($voucher->isUnredeemable())->toBeTrue();
    expect($voucher->unredeem($unknown))->toBeFalse();
    expect($voucher->isRedeemed())->toBeTrue();
    expect($voucher->redeemers)->not->toBeEmpty();

    expect($voucher->isUnredeemable())->toBeTrue();
    expect($voucher->unredeem($redeemer))->toBeTrue();
    expect($voucher->isRedeemed())->toBeFalse();
    expect($voucher->redeemers)->toBeEmpty();
});
