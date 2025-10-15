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

    // Temporarily overrides.
    Config::withModels(...$models);
    foreach ($models as $name => $model) {
        expect(Config::model($name))->toBe($model);
    }
    Config::resetModels();

    // Config overrides.
    config(['vouchers.models' => $models]);
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
    config(['vouchers.tables' => $tables]);
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
    $config = new Config();

    expect($config->getOptions())->toBeEmpty();
    expect($config->getCharacters())->toBe(config('vouchers.characters'));
    expect($config->getMask())->toBe(config('vouchers.mask'));
    expect($config->getPrefix())->toBe(config('vouchers.prefix'));
    expect($config->getSuffix())->toBe(config('vouchers.suffix'));
    expect($config->getSeparator())->toBe(config('vouchers.separator'));
});

/**
 * Test options overridden in config.
 */
test('config overridden options', function () {
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
        expect($value)->not->toBe($config->{$getter}());
        config(["vouchers.{$key}" => $value]);
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
        expect($value)->not->toBe($config->{$getter}());
        $config->{$setter}($value);
    }

    expect($config->getOptions())->toBe($options);
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

    // Test 'null' resets.
    $config
        ->withCharacters(null)
        ->withMask(null)
        ->withPrefix(null)
        ->withSuffix(null)
        ->withSeparator(null)
    ;
    expect($config->getCharacters())->toBe(config('vouchers.characters'));
    expect($config->getMask())->toBe(config('vouchers.mask'));
    expect($config->getPrefix())->toBe(config('vouchers.prefix'));
    expect($config->getSuffix())->toBe(config('vouchers.suffix'));
    expect($config->getSeparator())->toBe(config('vouchers.separator'));

    // Test 'withCode' call.
    $config->withCode('MYVOUCHER');
    expect($config->getMask())->toBe('MYVOUCHER');
    expect($config->getPrefix())->toBe('');
    expect($config->getSuffix())->toBe('');
    expect($config->getSeparator())->toBe('');
});

/**
 * Test additional options using 'with' methods.
 */
test('additional options', function () {
    $config = new Config();
    Carbon::setTestNow(Carbon::now());

    // Test metadata
    $metadata = [
        'limit' => 42,
        'foo'   => 'bar',
        'next'  => [
            'level' => 'shizzle',
        ],
    ];
    expect($config->withMetadata($metadata)->getMetadata())->toBe($metadata);
    expect($config->withMetadata(null)->getMetadata())->toBeNull();

    // Test start time.
    $interval = CarbonInterval::create(days: 10, minutes: 30, seconds: 45);
    expect(Carbon::now()->toDateTimeString())
        ->toBe($config->withStartTime(Carbon::now())->getStartTime()->toDateTimeString())
    ;
    expect($config->withStartTime(null)->getStartTime())->toBeNull();
    expect(Carbon::now()->add($interval)->toDateTimeString())
        ->toBe($config->withStartTimeIn($interval)->getStartTime()->toDateTimeString())
    ;
    expect($config->withStartTimeIn(null)->getStartTime())->toBeNull();
    expect(Carbon::now()->startOfDay()->toDateTimeString())
        ->toBe($config->withStartDate(Carbon::now())->getStartTime()->toDateTimeString())
    ;
    expect($config->withStartDate(null)->getStartTime())->toBeNull();
    expect(Carbon::now()->add($interval)->startOfDay()->toDateTimeString())
        ->toBe($config->withStartDateIn($interval)->getStartTime()->toDateTimeString())
    ;
    expect($config->withStartDateIn(null)->getStartTime())->toBeNull();

    // Test expire time.
    $interval = CarbonInterval::create(days: 15, minutes: 20, seconds: 10);
    expect(Carbon::now()->toDateTimeString())
        ->toBe($config->withExpireTime(Carbon::now())->getExpireTime()->toDateTimeString())
    ;
    expect($config->withExpireTime(null)->getExpireTime())->toBeNull();
    expect(Carbon::now()->add($interval)->toDateTimeString())
        ->toBe($config->withExpireTimeIn($interval)->getExpireTime()->toDateTimeString())
    ;
    expect($config->withExpireTimeIn(null)->getExpireTime())->toBeNull();
    expect(Carbon::now()->endOfDay()->toDateTimeString())
        ->toBe($config->withExpireDate(Carbon::now())->getExpireTime()->toDateTimeString())
    ;
    expect($config->withExpireDate(null)->getExpireTime())->toBeNull();
    expect(Carbon::now()->add($interval)->endOfDay()->toDateTimeString())
        ->toBe($config->withExpireDateIn($interval)->getExpireTime()->toDateTimeString())
    ;
    expect($config->withExpireDateIn(null)->getExpireTime())->toBeNull();

    // Test owner.
    $owner = User::factory()->make();
    expect($config->withOwner($owner)->getOwner())->toBe($owner);
    expect($config->withOwner(null)->getOwner())->toBeNull();

    // Test entities.
    $entities = Color::factory()->count(3)->make()->all();
    // Using spread operator.
    expect($config->withEntities(...$entities)->getEntities())->toBe($entities);
    // Using empty spread operator.
    expect($config->withEntities(...[])->getEntities())->toBe([]);
    // Using array.
    expect($config->withEntities($entities)->getEntities())->toBe($entities);
    // Using collection.
    expect($config->withEntities(collect($entities))->getEntities())->toBe($entities);
    // Using generator.
    expect($config->withEntities((function () use ($entities) {
        foreach ($entities as $entity) {
            yield $entity;
        }
    })())->getEntities())->toBe($entities);

    Carbon::setTestNow();
});
