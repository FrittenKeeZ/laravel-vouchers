<?php

declare(strict_types=1);

use Carbon\Carbon;
use Carbon\CarbonInterval;
use FrittenKeeZ\Vouchers\Config;
use FrittenKeeZ\Vouchers\Models\Redeemer;
use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Models\VoucherEntity;
use FrittenKeeZ\Vouchers\Tests\Models\Color;
use FrittenKeeZ\Vouchers\Tests\Models\User;

uses(FrittenKeeZ\Vouchers\Tests\TestCase::class);

/**
 * @internal
 */

/**
 * Test Config::model() method.
 */
test('model resolving', function () {
    // Test defaults.
    expect(Config::model('voucher'))->toBe(Voucher::class);
    expect(Config::model('entity'))->toBe(VoucherEntity::class);
    expect(Config::model('redeemer'))->toBe(Redeemer::class);

    // Test fake overrides.
    $models = [
        'entity'   => 'FakeEntity',
        'redeemer' => 'FakeRedeemer',
        'voucher'  => 'FakeVoucher',
    ];
    app()['config']->set('vouchers.models', $models);
    foreach ($models as $name => $model) {
        expect(Config::model($name))->toBe($model);
    }

    // Test non-existing.
    expect(Config::model('idontexist'))->toBeNull();
    expect(Config::model('idontexisteither'))->toBeNull();
});

/**
 * Test Config::table() method.
 */
test('table resolving', function () {
    // Test defaults.
    expect(Config::table('vouchers'))->toBe('vouchers');
    expect(Config::table('entities'))->toBe('voucher_entity');
    expect(Config::table('redeemers'))->toBe('redeemers');

    // Test fake overrides.
    $tables = [
        'entities'  => 'this_voucher_entity',
        'redeemers' => 'this_redeemers',
        'vouchers'  => 'this_vouchers',
    ];
    app()['config']->set('vouchers.tables', $tables);
    foreach ($tables as $name => $table) {
        expect(Config::table($name))->toBe($table);
    }

    // Test non-existing.
    expect(Config::table('idontexist'))->toBeNull();
    expect(Config::table('idontexisteither'))->toBeNull();
});

/**
 * Test default options from config.
 */
test('default options', function () {
    $config = new Config;
    $app_config = app()['config'];

    expect($config->getOptions())->toBeEmpty();
    expect($config->getCharacters())->toBe($app_config->get('vouchers.characters'));
    expect($config->getMask())->toBe($app_config->get('vouchers.mask'));
    expect($config->getPrefix())->toBe($app_config->get('vouchers.prefix'));
    expect($config->getSuffix())->toBe($app_config->get('vouchers.suffix'));
    expect($config->getSeparator())->toBe($app_config->get('vouchers.separator'));
});

/**
 * Test options overridden in config.
 */
test('config overridden options', function () {
    $config = new Config;
    $app_config = app()['config'];

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

    expect($config->getOptions())->toBeEmpty();
    expect($config->getCharacters())->toBe($options['characters']);
    expect($config->getMask())->toBe($options['mask']);
    expect($config->getPrefix())->toBe($options['prefix']);
    expect($config->getSuffix())->toBe($options['suffix']);
    expect($config->getSeparator())->toBe($options['separator']);
});

/**
 * Test dynamically overridden options using 'with' methods.
 */
test('dynamically overridden options', function () {
    $config = new Config;

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

    assertArrayStructure($options, $config->getOptions());
    expect($config->getCharacters())->toBe($options['characters']);
    expect($config->getMask())->toBe($options['mask']);
    expect($config->getPrefix())->toBe($options['prefix']);
    expect($config->getSuffix())->toBe($options['suffix']);
    expect($config->getSeparator())->toBe($options['separator']);

    // Test 'without' calls.
    $config->withoutPrefix()->withoutSuffix()->withoutSeparator();
    expect($config->getPrefix())->toBe('');
    expect($config->getSuffix())->toBe('');
    expect($config->getSeparator())->toBe('');
});

/**
 * Test additional options using 'with' methods.
 */
test('additional options', function () {
    $config = new Config;

    // Test metadata
    $metadata = [
        'limit' => 42,
        'foo'   => 'bar',
        'next'  => [
            'level' => 'shizzle',
        ],
    ];
    expect($config->withMetadata($metadata)->getMetadata())->toBe($metadata);

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
    $owner = User::factory()->make();
    expect($config->withOwner($owner)->getOwner())->toBe($owner);

    // Test entities.
    $entities = Color::factory()->count(3)->make()->all();
    expect($config->withEntities(...$entities)->getEntities())->toBe($entities);
});

// Helpers
/**
 * Assert array structure.
 */
function assertArrayStructure(array $expected, array $actual): void
{
    expect(empty(array_diff_key($expected, $actual)) && empty(array_diff_key($actual, $expected)))->toBeTrue();
}
