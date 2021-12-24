<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use FrittenKeeZ\Vouchers\Config;
use FrittenKeeZ\Vouchers\Models\Redeemer;
use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Models\VoucherEntity;
use FrittenKeeZ\Vouchers\Tests\Models\Color;
use FrittenKeeZ\Vouchers\Tests\Models\User;

/**
 * @internal
 */
class ConfigTest extends TestCase
{
    /**
     * Test Config::model() method.
     *
     * @return void
     */
    public function testModelResolving(): void
    {
        // Test defaults.
        $this->assertSame(Voucher::class, Config::model('voucher'));
        $this->assertSame(VoucherEntity::class, Config::model('entity'));
        $this->assertSame(Redeemer::class, Config::model('redeemer'));

        // Test fake overrides.
        $models = [
            'entity'   => 'FakeEntity',
            'redeemer' => 'FakeRedeemer',
            'voucher'  => 'FakeVoucher',
        ];
        $this->app['config']->set('vouchers.models', $models);
        foreach ($models as $name => $model) {
            $this->assertSame($model, Config::model($name));
        }

        // Test non-existing.
        $this->assertNull(Config::model('idontexist'));
        $this->assertNull(Config::model('idontexisteither'));
    }

    /**
     * Test Config::table() method.
     *
     * @return void
     */
    public function testTableResolving(): void
    {
        // Test defaults.
        $this->assertSame('vouchers', Config::table('vouchers'));
        $this->assertSame('voucher_entity', Config::table('entities'));
        $this->assertSame('redeemers', Config::table('redeemers'));

        // Test fake overrides.
        $tables = [
            'entities'  => 'this_voucher_entity',
            'redeemers' => 'this_redeemers',
            'vouchers'  => 'this_vouchers',
        ];
        $this->app['config']->set('vouchers.tables', $tables);
        foreach ($tables as $name => $table) {
            $this->assertSame($table, Config::table($name));
        }

        // Test non-existing.
        $this->assertNull(Config::table('idontexist'));
        $this->assertNull(Config::table('idontexisteither'));
    }

    /**
     * Test default options from config.
     *
     * @return void
     */
    public function testDefaultOptions(): void
    {
        $config = new Config();
        $app_config = $this->app['config'];

        $this->assertEmpty($config->getOptions());
        $this->assertSame($app_config->get('vouchers.characters'), $config->getCharacters());
        $this->assertSame($app_config->get('vouchers.mask'), $config->getMask());
        $this->assertSame($app_config->get('vouchers.prefix'), $config->getPrefix());
        $this->assertSame($app_config->get('vouchers.suffix'), $config->getSuffix());
        $this->assertSame($app_config->get('vouchers.separator'), $config->getSeparator());
    }

    /**
     * Test options overridden in config.
     *
     * @return void
     */
    public function testConfigOverriddenOptions(): void
    {
        $config = new Config();
        $app_config = $this->app['config'];

        // Override config.
        $options = [
            'characters' => '1234567890',
            'mask'       => '***-***-***',
            'prefix'     => 'foo',
            'suffix'     => 'bar',
            'separator'  => '_',
        ];
        foreach ($options as $key => $value) {
            $getter = 'get' . ucfirst($key);
            $this->assertNotSame($value, $config->{$getter}());
            $app_config->set('vouchers.' . $key, $value);
        }

        $this->assertEmpty($config->getOptions());
        $this->assertSame($options['characters'], $config->getCharacters());
        $this->assertSame($options['mask'], $config->getMask());
        $this->assertSame($options['prefix'], $config->getPrefix());
        $this->assertSame($options['suffix'], $config->getSuffix());
        $this->assertSame($options['separator'], $config->getSeparator());
    }

    /**
     * Test dynamically overridden options using 'with' methods.
     *
     * @return void
     */
    public function testDynamicallyOverriddenOptions(): void
    {
        $config = new Config();

        // Override config.
        $options = [
            'characters' => '1234567890',
            'mask'       => '***-***-***',
            'prefix'     => 'foo',
            'suffix'     => 'bar',
            'separator'  => '_',
        ];
        foreach ($options as $key => $value) {
            $getter = 'get' . ucfirst($key);
            $setter = 'with' . ucfirst($key);
            $this->assertNotSame($value, $config->{$getter}());
            $config->{$setter}($value);
        }

        $this->assertArrayStructure($options, $config->getOptions());
        $this->assertSame($options['characters'], $config->getCharacters());
        $this->assertSame($options['mask'], $config->getMask());
        $this->assertSame($options['prefix'], $config->getPrefix());
        $this->assertSame($options['suffix'], $config->getSuffix());
        $this->assertSame($options['separator'], $config->getSeparator());

        // Test 'without' calls.
        $config
            ->withoutPrefix()
            ->withoutSuffix()
            ->withoutSeparator()
        ;
        $this->assertSame('', $config->getPrefix());
        $this->assertSame('', $config->getSuffix());
        $this->assertSame('', $config->getSeparator());
    }

    /**
     * Test additional options using 'with' methods.
     *
     * @return void
     */
    public function testAdditionalOptions(): void
    {
        $config = new Config();

        // Test metadata
        $metadata = [
            'limit' => 42,
            'foo'   => 'bar',
            'next'  => [
                'level' => 'shizzle',
            ],
        ];
        $this->assertSame($metadata, $config->withMetadata($metadata)->getMetadata());

        // Test start time.
        $interval = CarbonInterval::create('P10DT30M45S');
        $this->assertSame(
            Carbon::now()->toDateTimeString(),
            $config->withStartTime(Carbon::now())->getStartTime()->toDateTimeString()
        );
        $this->assertSame(
            Carbon::now()->add($interval)->toDateTimeString(),
            $config->withStartTimeIn($interval)->getStartTime()->toDateTimeString()
        );
        $this->assertSame(
            Carbon::now()->startOfDay()->toDateTimeString(),
            $config->withStartDate(Carbon::now())->getStartTime()->toDateTimeString()
        );
        $this->assertSame(
            Carbon::now()->add($interval)->startOfDay()->toDateTimeString(),
            $config->withStartDateIn($interval)->getStartTime()->toDateTimeString()
        );

        // Test expire time.
        $interval = CarbonInterval::create('P15DT20M10S');
        $this->assertSame(
            Carbon::now()->toDateTimeString(),
            $config->withExpireTime(Carbon::now())->getExpireTime()->toDateTimeString()
        );
        $this->assertSame(
            Carbon::now()->add($interval)->toDateTimeString(),
            $config->withExpireTimeIn($interval)->getExpireTime()->toDateTimeString()
        );
        $this->assertSame(
            Carbon::now()->endOfDay()->toDateTimeString(),
            $config->withExpireDate(Carbon::now())->getExpireTime()->toDateTimeString()
        );
        $this->assertSame(
            Carbon::now()->add($interval)->endOfDay()->toDateTimeString(),
            $config->withExpireDateIn($interval)->getExpireTime()->toDateTimeString()
        );

        // Test owner.
        $owner = $this->factory(User::class)->make();
        $this->assertSame($owner, $config->withOwner($owner)->getOwner());

        // Test entities.
        $entities = $this->factory(Color::class, 3)->make()->all();
        $this->assertSame($entities, $config->withEntities(...$entities)->getEntities());
    }

    /**
     * Assert array structure.
     *
     * @param array $expected
     * @param array $actual
     *
     * @return void
     */
    protected function assertArrayStructure(array $expected, array $actual): void
    {
        $this->assertTrue(empty(array_diff_key($expected, $actual)) && empty(array_diff_key($actual, $expected)));
    }
}
