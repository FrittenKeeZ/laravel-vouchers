<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Tests\Models\Color;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Vouchers;

/**
 * Test HasVouchers::createVoucher().
 */
test('create voucher', function () {
    $user = User::factory()->create();
    $voucher = $user->createVoucher();

    // Check user voucher relation.
    expect($user->is($voucher->owner))->toBeTrue();
    expect($voucher->is($user->vouchers->first()))->toBeTrue();
});

/**
 * Test HasVouchers::createVoucher() with callback.
 */
test('create voucher with callback', function () {
    $user = User::factory()->create();
    $color = Color::factory()->create();
    $voucher = $user->createVoucher(function (Vouchers $vouchers) use ($color) {
        $vouchers->withEntities($color);
    });

    // Check user voucher relation.
    expect($user->is($voucher->owner))->toBeTrue();
    expect($voucher->is($user->vouchers->first()))->toBeTrue();
    expect($color->is($voucher->getEntities(Color::class)->first()))->toBeTrue();
});

/**
 * Test HasVouchers::createVoucher() with associated entities.
 */
test('create voucher with associated', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $voucher = $user->createVoucher(function (Vouchers $vouchers) use ($other) {
        $vouchers->withEntities($other);
    });

    // Check user voucher relation.
    expect($user->is($voucher->owner))->toBeTrue();
    expect($voucher->is($user->vouchers->first()))->toBeTrue();
    expect($other->is($voucher->owner))->toBeFalse();
    expect($other->is($voucher->getEntities(User::class)->first()))->toBeTrue();
    expect($voucher->is($other->voucherEntities->first()->voucher))->toBeTrue();
    expect($voucher->is($other->associatedVouchers->first()))->toBeTrue();
});

/**
 * Test HasVouchers::createVouchers().
 */
test('create vouchers', function () {
    $user = User::factory()->create();
    $vouchers = $user->createVouchers(3);

    foreach ($vouchers as $index => $voucher) {
        // Check user voucher relation.
        expect($user->is($voucher->owner))->toBeTrue();
        expect($voucher->is($user->vouchers[$index]))->toBeTrue();
    }
});

/**
 * Test HasVouchers::createVouchers() with callback.
 */
test('create vouchers with callback', function () {
    $user = User::factory()->create();
    $color = Color::factory()->create();
    $vouchers = $user->createVouchers(3, function (Vouchers $vouchers) use ($color) {
        $vouchers->withEntities($color);
    });

    foreach ($vouchers as $index => $voucher) {
        // Check user voucher relation.
        expect($user->is($voucher->owner))->toBeTrue();
        expect($voucher->is($user->vouchers[$index]))->toBeTrue();
        expect($color->is($voucher->getEntities(Color::class)->first()))->toBeTrue();
    }
});
