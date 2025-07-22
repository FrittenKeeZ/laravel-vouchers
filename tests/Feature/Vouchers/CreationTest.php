<?php

declare(strict_types=1);

use Carbon\Carbon;
use Carbon\CarbonInterval;
use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Vouchers;

/**
 * Test voucher creation.
 */
test('voucher creation', function () {
    $vouchers = new Vouchers();

    // Simple voucher.
    $voucher = $vouchers->create();
    expect($voucher)->toBeInstanceOf(Voucher::class);
    expect($voucher->metadata)->toBeNull();
    expect($voucher->starts_at)->toBeNull();
    expect($voucher->expires_at)->toBeNull();
    expect($voucher->getEntities())->toBeEmpty();

    // With metdata, start time and expire time.
    $metadata = ['foo' => 'bar', 'baz' => 'boom'];
    $now = Carbon::now();
    $start_time = $now->copy()->add(CarbonInterval::create(days: 1));
    $expire_time = $now->copy()->add(CarbonInterval::create(days: 30));
    $user = User::factory()->create();
    $users = User::factory()->count(3)->create();
    $voucher = $vouchers
        ->withMetadata($metadata)
        ->withStartTime($start_time)
        ->withExpireTime($expire_time)
        ->withOwner($user)
        ->withEntities(...$users->all())
        ->create()
    ;
    expect($voucher)->toBeInstanceOf(Voucher::class);
    expect($voucher->metadata)->toBe($metadata);
    expect($voucher->starts_at->toDateTimeString())->toBe($start_time->toDateTimeString());
    expect($voucher->expires_at->toDateTimeString())->toBe($expire_time->toDateTimeString());
    expect($user->is($voucher->owner))->toBeTrue();
    foreach ($voucher->getEntities() as $index => $entity) {
        expect($users[$index]->is($entity))->toBeTrue();
    }

    // Test amount.
    $amount = 5;
    $batch = $vouchers->create($amount);
    expect($batch)->toHaveCount($amount);
    foreach ($batch as $voucher) {
        expect($voucher)->toBeInstanceOf(Voucher::class);
    }

    // Test negative amount.
    expect($vouchers->create(-5))->toBeEmpty();
});
