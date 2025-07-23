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
 * Test redeemers morph relationship.
 */
test('redeemers morph relationship', function () {
    $user = User::factory()->create();
    Redeemer::factory()->for($user, 'redeemer')->for(Voucher::factory()->redeemed())->create();
    Redeemer::factory()->for($user, 'redeemer')->for(Voucher::factory()->redeemed())->create();

    expect($user->redeemers)->toHaveCount(2);
});
