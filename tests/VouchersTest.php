<?php

namespace FrittenKeeZ\Vouchers\Tests;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use PHPUnit\Runner\Version;
use FrittenKeeZ\Vouchers\Vouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Models\Redeemer;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Exceptions\VoucherNotFoundException;
use FrittenKeeZ\Vouchers\Exceptions\VoucherAlreadyRedeemedException;

class VouchersTest extends TestCase
{
    /**
     * Test vouchers instance through app::make().
     *
     * @return void
     */
    public function testInstance(): void
    {
        $this->assertInstanceOf(Vouchers::class, $this->app->make('vouchers'));
    }

    /**
     * Test that Vouchers::getConfig() returns clone and not same instance.
     *
     * @return void
     */
    public function testConfigClone(): void
    {
        $vouchers = new Vouchers();
        $config = $vouchers->getConfig();

        $this->assertNotSame($config, $vouchers->getConfig());
    }

    /**
     * Test code generation.
     *
     * @return void
     */
    public function testCodeGeneration(): void
    {
        $vouchers = new Vouchers();
        $config = $vouchers->getConfig();

        // Grab mask, characters and validation regex.
        $mask = $config->getMask();
        $characters = $config->getCharacters();
        $prefix = $config->getPrefix();
        $suffix = $config->getSuffix();
        $separator = $config->getSeparator();
        $regex = $this->generateCodeValidationRegex($mask, $characters, $prefix, $suffix, $separator);

        $regexAssertMethod = 'assertRegExp';
        if ((float) Version::series() >= 9.1) {
            $regexAssertMethod = 'assertMatchesRegularExpression';
        }

        // Test single generation.
        $this->$regexAssertMethod($regex, $vouchers->generate($mask, $characters));

        // Test batch operation.
        foreach ($vouchers->batch(10) as $code) {
            $this->$regexAssertMethod($regex, $code);
        }

        // Test negative batch amount.
        $this->assertEmpty($vouchers->batch(-10));
    }

    /**
     * Test voucher creation.
     *
     * @return void
     */
    public function testVoucherCreation(): void
    {
        $vouchers = new Vouchers();

        // Simple voucher.
        $voucher = $vouchers->create();
        $this->assertInstanceOf(Voucher::class, $voucher);
        $this->assertNull($voucher->metadata);
        $this->assertNull($voucher->starts_at);
        $this->assertNull($voucher->expires_at);
        $this->assertEmpty($voucher->getEntities());

        // With metdata, start time and expire time.
        $metadata = ['foo' => 'bar', 'baz' => 'boom'];
        $now = Carbon::now();
        $startInterval = CarbonInterval::create('P1D');
        $expireInterval = CarbonInterval::create('P30D');
        $users = factory(User::class, 3)->create();
        $voucher = $vouchers
            ->withMetadata($metadata)
            ->withStartTimeIn($startInterval)
            ->withExpireTimeIn($expireInterval)
            ->withEntities(...$users->all())
            ->create()
        ;
        $this->assertInstanceOf(Voucher::class, $voucher);
        $this->assertSame($metadata, $voucher->metadata);
        $this->assertSame(
            $now->copy()->add($startInterval)->toDateTimeString(),
            $voucher->starts_at->toDateTimeString()
        );
        $this->assertSame(
            $now->copy()->add($expireInterval)->toDateTimeString(),
            $voucher->expires_at->toDateTimeString()
        );
        foreach ($voucher->getEntities() as $index => $entity) {
            $this->assertTrue($users[$index]->is($entity));
        }

        // Test amount.
        $amount = 10;
        $batch = $vouchers->create($amount);
        $this->assertSame($amount, count($batch));
        foreach ($batch as $voucher) {
            $this->assertInstanceOf(Voucher::class, $voucher);
        }

        // Test negative amount.
        $this->assertEmpty($vouchers->create(-10));
    }

    /**
     * Test voucher redemption.
     *
     * @return void
     */
    public function testVoucherRedemption(): void
    {
        $vouchers = new Vouchers();
        $user = factory(User::class)->create();
        $voucher = $vouchers->withEntities($user)->create();

        // Check user voucher relation.
        $this->assertTrue($voucher->is($user->vouchers->first()));
        $this->assertTrue($voucher->is($user->voucherEntities->first()->voucher));

        // Check voucher states.
        $this->assertTrue($voucher->isRedeemable());
        $this->assertTrue($vouchers->redeemable($voucher->code));
        $this->assertEmpty($voucher->redeemers);
        $this->assertNotEmpty($voucher->getEntities());
        $this->assertEmpty($voucher->getEntities('FakeClass'));
        $metadata = ['foo' => 'bar', 'baz' => 'boom'];
        $this->assertTrue($vouchers->redeem($voucher->code, $user, $metadata));
        // Refresh instance.
        $voucher->refresh();
        $this->assertFalse($voucher->isRedeemable());
        $this->assertFalse($vouchers->redeemable($voucher->code));
        $this->assertNotEmpty($voucher->redeemers);
        $redeemer = $voucher->redeemers->first();
        $this->assertInstanceOf(Redeemer::class, $redeemer);
        $this->assertTrue($user->is($redeemer->redeemer));
        $this->assertSame($metadata, $redeemer->metadata);
        $this->assertTrue($redeemer->is($user->redeemers->first()));
        $this->assertTrue($voucher->is($redeemer->voucher));
    }

    /**
     * Test voucher not found exception.
     *
     * @return void
     */
    public function testVoucherNotFoundException(): void
    {
        $vouchers = new Vouchers();
        $user = factory(User::class)->create();

        $this->expectException(VoucherNotFoundException::class);
        $vouchers->redeem('idonotexist', $user);
    }

    /**
     * Test voucher already redeemed exception.
     *
     * @return void
     */
    public function testVoucherAlreadyRedeemedException(): void
    {
        $vouchers = new Vouchers();
        $voucher = $vouchers->create();
        $user = factory(User::class)->create();

        $this->assertTrue($vouchers->redeem($voucher->code, $user));
        $this->expectException(VoucherAlreadyRedeemedException::class);
        $vouchers->redeem($voucher->code, $user);
    }

    /**
     * Test Vouchers::wrap() method.
     *
     * @dataProvider wrapProvider
     *
     * @param  string       $str
     * @param  string|null  $prefix
     * @param  string|null  $suffix
     * @param  string       $separator
     * @param  string       $expected
     * @return void
     */
    public function testStringWrapping(
        string $str,
        ?string $prefix,
        ?string $suffix,
        string $separator,
        string $expected
    ): void {
        $this->assertSame($expected, (new Vouchers)->wrap($str, $prefix, $suffix, $separator));
    }

    /**
     * Test invalid magic call (Vouchers::__call()).
     *
     * @return void
     */
    public function testInvalidMagicCall(): void
    {
        $this->expectException('ErrorException');
        $vouchers = new Vouchers();
        $vouchers->methodthatdoesnotexist();
    }

    /**
     * Data provider for Vouchers::wrap().
     *
     * @return array
     */
    public function wrapProvider(): array
    {
        return [
            'string only' => ['code', null, null, '-', 'code'],
            'prefix dash separator' => ['code', 'foo', null, '-', 'foo-code'],
            'suffix dash separator' => ['code', null, 'bar', '-', 'code-bar'],
            'prefix suffix dash separator' => ['code', 'foo', 'bar', '-', 'foo-code-bar'],
            'prefix suffix underscore separator' => ['code', 'foo', 'bar', '_', 'foo_code_bar'],
        ];
    }

    /**
     * Generate regex to validate a code generated with a specific mask, character list, prefix, suffix and separator.
     *
     * @param  string  $mask
     * @param  string  $characters
     * @return string
     */
    protected function generateCodeValidationRegex(
        string $mask,
        string $characters,
        ?string $prefix,
        ?string $suffix,
        string $separator
    ): string {
        $match = preg_quote($characters, '/');
        $inner = preg_replace_callback('/(?:\\\\\*)+/', function (array $matches) use ($match) {
            return sprintf('[%s]{%d}', $match, mb_strlen($matches[0]) / 2);
        }, preg_quote($mask, '/'));

        return sprintf(
            '/%s%s%s/',
            empty($prefix) ? '' : preg_quote($prefix . $separator, '/'),
            $inner,
            empty($suffix) ? '' : preg_quote($separator . $suffix, '/')
        );
    }
}
