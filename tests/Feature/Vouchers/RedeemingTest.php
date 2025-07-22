<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Models\Redeemer;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Vouchers;

/**
 * Test voucher redeeming.
 */
test('voucher redeeming', function () {
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
