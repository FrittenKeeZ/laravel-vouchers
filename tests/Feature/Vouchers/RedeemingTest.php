<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Models\Redeemer;
use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Vouchers;

/**
 * Test voucher redeeming single.
 */
test('voucher redeeming single', function () {
    $vouchers = new Vouchers();
    $user = User::factory()->create();
    $voucher = $vouchers->withOwner($user)->create();

    // Check user voucher relation.
    expect($user->is($voucher->owner))->toBeTrue();
    expect($voucher->is($user->vouchers->first()))->toBeTrue();

    // Check voucher states.
    expect($voucher->isRedeemable())->toBeTrue();
    expect($vouchers->redeemable($voucher->code))->toBeTrue();
    expect($voucher->redeemers)->toBeEmpty();
    expect($voucher->getEntities())->toBeEmpty();

    $metadata = ['foo' => 'bar', 'baz' => 'boom'];
    expect($vouchers->redeem($voucher->code, $user, $metadata))->toBeTrue();

    // Refresh instance.
    $voucher->refresh();
    expect($voucher->isRedeemable())->toBeFalse();
    expect($vouchers->redeemable($voucher->code))->toBeFalse();
    expect($voucher->redeemers)->not->toBeEmpty();

    $redeemer = $voucher->redeemers->first();
    expect($redeemer)->toBeInstanceOf(Redeemer::class);
    expect($user->is($redeemer->redeemer))->toBeTrue();
    expect($redeemer->metadata)->toBe($metadata);
    expect($redeemer->is($user->redeemers->first()))->toBeTrue();
    expect($voucher->is($redeemer->voucher))->toBeTrue();
});

/**
 * Test voucher redeeming multiple.
 */
test('voucher redeeming multiple', function () {
    Voucher::shouldMarkRedeemed(function (Voucher $voucher) {
        $voucher->metadata = array_merge($voucher->metadata, ['amount' => $voucher->metadata['amount'] - 1]);

        return $voucher->metadata['amount'] <= 0;
    });

    $vouchers = new Vouchers();
    $voucher = $vouchers->withMetadata(['amount' => 3])->create();

    expect($voucher->isRedeemable())->toBeTrue();
    expect($voucher->redeemers)->toBeEmpty();

    // First redeeming - voucher should be redeemable.
    expect($vouchers->redeem($voucher->code, User::factory()->create()))->toBeTrue();
    $voucher->refresh();
    expect($voucher->isRedeemable())->toBeTrue();
    expect($voucher->redeemers)->toHaveCount(1);

    // Second redeeming - voucher should be redeemable.
    expect($vouchers->redeem($voucher->code, User::factory()->create()))->toBeTrue();
    $voucher->refresh();
    expect($voucher->isRedeemable())->toBeTrue();
    expect($voucher->redeemers)->toHaveCount(2);

    // Third redeeming - voucher should not be redeemable.
    expect($vouchers->redeem($voucher->code, User::factory()->create()))->toBeTrue();
    $voucher->refresh();
    expect($voucher->isRedeemable())->toBeFalse();
    expect($voucher->redeemers)->toHaveCount(3);
});
