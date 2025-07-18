<?php

declare(strict_types=1);

use Carbon\Carbon;
use Carbon\CarbonInterval;
use FrittenKeeZ\Vouchers\Models\Redeemer;
use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Vouchers;

/**
 * Generate regex to validate a code generated with a specific mask, character list, prefix, suffix and separator.
 */
function _generate_code_validation_regex(
    string $mask,
    string $characters,
    ?string $prefix,
    ?string $suffix,
    string $separator
): string {
    $match = preg_quote($characters, '/');
    $inner = preg_replace_callback(
        "/(?:\\\\\*)+/",
        fn (array $matches) => sprintf('[%s]{%d}', $match, mb_strlen($matches[0]) / 2),
        preg_quote($mask, '/')
    );

    return sprintf(
        '/%s%s%s/',
        empty($prefix) ? '' : preg_quote($prefix . $separator, '/'),
        $inner,
        empty($suffix) ? '' : preg_quote($separator . $suffix, '/')
    );
}

/**
 * Test code generation.
 */
test('code generation', function () {
    $vouchers = new Vouchers();
    $config = $vouchers->getConfig();

    // Grab mask, characters, prefix, suffix and separator.
    $mask = $config->getMask();
    $characters = $config->getCharacters();
    $prefix = $config->getPrefix();
    $suffix = $config->getSuffix();
    $separator = $config->getSeparator();

    // Check vouchers proxy call to config.
    expect($vouchers->getMask())->toBe($mask);
    expect($vouchers->getCharacters())->toBe($characters);
    expect($vouchers->getPrefix())->toBe($prefix);
    expect($vouchers->getSuffix())->toBe($suffix);
    expect($vouchers->getSeparator())->toBe($separator);

    // Grab validation regex.
    $regex = _generate_code_validation_regex($mask, $characters, $prefix, $suffix, $separator);

    // Test single generation.
    expect($vouchers->generate($mask, $characters))->toMatch($regex);

    // Test batch operation.
    foreach ($vouchers->batch(10) as $code) {
        expect($code)->toMatch($regex);
    }

    // Test negative batch amount.
    expect($vouchers->batch(-10))->toBeEmpty();
});

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
    $amount = 10;
    $batch = $vouchers->create($amount);
    expect(\count($batch))->toBe($amount);
    foreach ($batch as $voucher) {
        expect($voucher)->toBeInstanceOf(Voucher::class);
    }

    // Test negative amount.
    expect($vouchers->create(-10))->toBeEmpty();
});

/**
 * Test voucher redemption.
 */
test('voucher redemption', function () {
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
