<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Tests\Models\Color;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use Illuminate\Support\Carbon;

/**
 * @internal
 */
class VoucherScopesTest extends TestCase
{
    /**
     * Test Voucher::scopeCode().
     */
    public function testCodeScope(): void
    {
        $voucher = Vouchers::create();

        $this->assertTrue(Voucher::code($voucher->code)->exists());
        $this->assertFalse(Voucher::code('NOPE')->exists());
    }

    /**
     * Test Voucher::scopeHasPrefix() and Voucher::scopeHasSuffix().
     */
    public function testPrefixAndSuffixScopes(): void
    {
        Voucher::create(['code' => 'FOOTESTBAR']);
        Voucher::create(['code' => 'FOOTESTBAZ']);
        Voucher::create(['code' => 'FUUTESTBAR']);
        Voucher::create(['code' => 'FUUTESTBAZ']);
        Voucher::create(['code' => 'FOO-TEST-BAR']);
        Voucher::create(['code' => 'FOO-TEST-BAZ']);
        Voucher::create(['code' => 'FUU-TEST-BAR']);
        Voucher::create(['code' => 'FUU-TEST-BAZ']);

        // Test prefix scope with separator.
        $this->assertSame(2, Voucher::withPrefix('FOO')->count());
        $this->assertSame(2, Voucher::withPrefix('FOO', '-')->count());
        $this->assertSame(2, Voucher::withPrefix('FUU')->count());
        $this->assertSame(2, Voucher::withPrefix('FUU', '-')->count());
        $this->assertSame(6, Voucher::withoutPrefix('FOO')->count());
        $this->assertSame(6, Voucher::withoutPrefix('FOO', '-')->count());
        $this->assertSame(6, Voucher::withoutPrefix('FUU')->count());
        $this->assertSame(6, Voucher::withoutPrefix('FUU', '-')->count());
        // Test prefix scope without separator
        $this->assertSame(4, Voucher::withPrefix('FOO', '')->count());
        $this->assertSame(4, Voucher::withPrefix('FUU', '')->count());
        $this->assertSame(4, Voucher::withoutPrefix('FOO', '')->count());
        $this->assertSame(4, Voucher::withoutPrefix('FUU', '')->count());

        // Test suffix scope with separator.
        $this->assertSame(2, Voucher::withSuffix('BAR')->count());
        $this->assertSame(2, Voucher::withSuffix('BAR', '-')->count());
        $this->assertSame(2, Voucher::withSuffix('BAZ')->count());
        $this->assertSame(2, Voucher::withSuffix('BAZ', '-')->count());
        $this->assertSame(6, Voucher::withoutSuffix('BAR')->count());
        $this->assertSame(6, Voucher::withoutSuffix('BAR', '-')->count());
        $this->assertSame(6, Voucher::withoutSuffix('BAZ')->count());
        $this->assertSame(6, Voucher::withoutSuffix('BAZ', '-')->count());
        // Test suffix scope without separator
        $this->assertSame(4, Voucher::withSuffix('BAR', '')->count());
        $this->assertSame(4, Voucher::withSuffix('BAZ', '')->count());
        $this->assertSame(4, Voucher::withoutSuffix('BAR', '')->count());
        $this->assertSame(4, Voucher::withoutSuffix('BAZ', '')->count());

        // Test prefix and suffix scopes together with separator.
        $this->assertSame(1, Voucher::withPrefix('FOO')->withSuffix('BAR')->count());
        $this->assertSame(1, Voucher::withPrefix('FOO', '-')->withSuffix('BAR', '-')->count());
        $this->assertSame(1, Voucher::withPrefix('FUU')->withSuffix('BAR')->count());
        $this->assertSame(1, Voucher::withPrefix('FUU', '-')->withSuffix('BAR', '-')->count());
        $this->assertSame(1, Voucher::withPrefix('FOO')->withSuffix('BAZ')->count());
        $this->assertSame(1, Voucher::withPrefix('FOO', '-')->withSuffix('BAZ', '-')->count());
        $this->assertSame(1, Voucher::withPrefix('FUU')->withSuffix('BAZ')->count());
        $this->assertSame(1, Voucher::withPrefix('FUU', '-')->withSuffix('BAZ', '-')->count());
        $this->assertSame(1, Voucher::withoutPrefix('FOO')->withSuffix('BAR')->count());
        $this->assertSame(1, Voucher::withoutPrefix('FOO', '-')->withSuffix('BAR', '-')->count());
        $this->assertSame(1, Voucher::withoutPrefix('FUU')->withSuffix('BAR')->count());
        $this->assertSame(1, Voucher::withoutPrefix('FUU', '-')->withSuffix('BAR', '-')->count());
        $this->assertSame(1, Voucher::withoutPrefix('FOO')->withSuffix('BAZ')->count());
        $this->assertSame(1, Voucher::withoutPrefix('FOO', '-')->withSuffix('BAZ', '-')->count());
        $this->assertSame(1, Voucher::withoutPrefix('FUU')->withSuffix('BAZ')->count());
        $this->assertSame(1, Voucher::withoutPrefix('FUU', '-')->withSuffix('BAZ', '-')->count());
        $this->assertSame(1, Voucher::withPrefix('FOO')->withoutSuffix('BAR')->count());
        $this->assertSame(1, Voucher::withPrefix('FOO', '-')->withoutSuffix('BAR', '-')->count());
        $this->assertSame(1, Voucher::withPrefix('FUU')->withoutSuffix('BAR')->count());
        $this->assertSame(1, Voucher::withPrefix('FUU', '-')->withoutSuffix('BAR', '-')->count());
        $this->assertSame(1, Voucher::withPrefix('FOO')->withoutSuffix('BAZ')->count());
        $this->assertSame(1, Voucher::withPrefix('FOO', '-')->withoutSuffix('BAZ', '-')->count());
        $this->assertSame(1, Voucher::withPrefix('FUU')->withoutSuffix('BAZ')->count());
        $this->assertSame(1, Voucher::withPrefix('FUU', '-')->withoutSuffix('BAZ', '-')->count());
        $this->assertSame(5, Voucher::withoutPrefix('FOO')->withoutSuffix('BAR')->count());
        $this->assertSame(5, Voucher::withoutPrefix('FOO', '-')->withoutSuffix('BAR', '-')->count());
        $this->assertSame(5, Voucher::withoutPrefix('FUU')->withoutSuffix('BAR')->count());
        $this->assertSame(5, Voucher::withoutPrefix('FUU', '-')->withoutSuffix('BAR', '-')->count());
        $this->assertSame(5, Voucher::withoutPrefix('FOO')->withoutSuffix('BAZ')->count());
        $this->assertSame(5, Voucher::withoutPrefix('FOO', '-')->withoutSuffix('BAZ', '-')->count());
        $this->assertSame(5, Voucher::withoutPrefix('FUU')->withoutSuffix('BAZ')->count());
        $this->assertSame(5, Voucher::withoutPrefix('FUU', '-')->withoutSuffix('BAZ', '-')->count());
        // Test prefix and suffix scopes together without separator
        $this->assertSame(2, Voucher::withPrefix('FOO', '')->withSuffix('BAR', '')->count());
        $this->assertSame(2, Voucher::withPrefix('FUU', '')->withSuffix('BAR', '')->count());
        $this->assertSame(2, Voucher::withPrefix('FOO', '')->withSuffix('BAZ', '')->count());
        $this->assertSame(2, Voucher::withPrefix('FUU', '')->withSuffix('BAZ', '')->count());
        $this->assertSame(2, Voucher::withoutPrefix('FOO', '')->withSuffix('BAR', '')->count());
        $this->assertSame(2, Voucher::withoutPrefix('FUU', '')->withSuffix('BAR', '')->count());
        $this->assertSame(2, Voucher::withoutPrefix('FOO', '')->withSuffix('BAZ', '')->count());
        $this->assertSame(2, Voucher::withoutPrefix('FUU', '')->withSuffix('BAZ', '')->count());
        $this->assertSame(2, Voucher::withPrefix('FOO', '')->withoutSuffix('BAR', '')->count());
        $this->assertSame(2, Voucher::withPrefix('FUU', '')->withoutSuffix('BAR', '')->count());
        $this->assertSame(2, Voucher::withPrefix('FOO', '')->withoutSuffix('BAZ', '')->count());
        $this->assertSame(2, Voucher::withPrefix('FUU', '')->withoutSuffix('BAZ', '')->count());
        $this->assertSame(2, Voucher::withoutPrefix('FOO', '')->withoutSuffix('BAR', '')->count());
        $this->assertSame(2, Voucher::withoutPrefix('FUU', '')->withoutSuffix('BAR', '')->count());
        $this->assertSame(2, Voucher::withoutPrefix('FOO', '')->withoutSuffix('BAZ', '')->count());
        $this->assertSame(2, Voucher::withoutPrefix('FUU', '')->withoutSuffix('BAZ', '')->count());
    }

    /**
     * Test Voucher::scopeWithStarted().
     */
    public function testStartedScope(): void
    {
        Vouchers::create();
        Vouchers::withStartTime(Carbon::now()->subDay())->create();
        Vouchers::withStartTime(Carbon::now()->addDay())->create();

        $this->assertSame(3, Voucher::count());
        $this->assertSame(2, Voucher::withStarted()->count());
        $this->assertSame(1, Voucher::withoutStarted()->count());
    }

    /**
     * Test Voucher::scopeWithExpired().
     */
    public function testExpiredScope(): void
    {
        Vouchers::create();
        Vouchers::withExpireTime(Carbon::now()->subDay())->create();
        Vouchers::withExpireTime(Carbon::now()->addDay())->create();

        $this->assertSame(3, Voucher::count());
        $this->assertSame(1, Voucher::withExpired()->count());
        $this->assertSame(2, Voucher::withoutExpired()->count());
    }

    /**
     * Test Voucher::scopeWithRedeemed().
     */
    public function testRedeemedScope(): void
    {
        Vouchers::create();
        Vouchers::create()->update(['redeemed_at' => Carbon::now()->subDay()]);

        $this->assertSame(2, Voucher::count());
        $this->assertSame(1, Voucher::withRedeemed()->count());
        $this->assertSame(1, Voucher::withoutRedeemed()->count());
    }

    /**
     * Test Voucher::scopeWithRedeemable().
     */
    public function testRedeemableScope(): void
    {
        Vouchers::create();
        Vouchers::withStartTime(Carbon::now()->subDay())->create();
        Vouchers::withStartTime(Carbon::now()->addDay())->create();
        Vouchers::withExpireTime(Carbon::now()->subDay())->create();
        Vouchers::withExpireTime(Carbon::now()->addDay())->create();
        Vouchers::create()->update(['redeemed_at' => Carbon::now()->subDay()]);

        $this->assertSame(6, Voucher::count());
        $this->assertSame(3, Voucher::withRedeemable()->count());
        $this->assertSame(3, Voucher::withoutRedeemable()->count());
    }

    /**
     * Test Voucher::scopeWithEntities().
     */
    public function testEntitiesScope(): void
    {
        Vouchers::create();
        Vouchers::withEntities(...Color::factory()->count(3)->create())->create();
        Vouchers::withEntities(...User::factory()->count(3)->create())->create();
        Vouchers::withEntities(
            ...Color::factory()->count(3)->create(),
            ...User::factory()->count(3)->create()
        )->create();

        $this->assertSame(4, Voucher::count());
        $this->assertSame(3, Voucher::withEntities()->count());
        $this->assertSame(2, Voucher::withEntities(Color::class)->count());
        $this->assertSame(2, Voucher::withEntities(User::class)->count());
    }

    /**
     * Test Voucher::scopeWithOwnerType() and Voucher::scopeWithOwner().
     */
    public function testOwnerScopes(): void
    {
        // Create users.
        $first = User::factory()->create();
        $second = User::factory()->create();
        $third = User::factory()->create();

        // Create vouchers.
        Vouchers::create(2);
        $first->createVoucher();
        $second->createVouchers(2);
        $third->createVouchers(3);

        $this->assertSame(8, Voucher::count());
        $this->assertSame(2, Voucher::withoutOwner()->count());
        $this->assertSame(6, Voucher::withOwnerType(User::class)->count());
        $this->assertSame(1, Voucher::withOwner($first)->count());
        $this->assertSame(2, Voucher::withOwner($second)->count());
        $this->assertSame(3, Voucher::withOwner($third)->count());
    }
}
