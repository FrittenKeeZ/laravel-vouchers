<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Vouchers;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Test metadata is cast to a fluent array object.
 */
test('metadata is cast to array object', function () {
    $voucher = (new Vouchers())->withMetadata(['foo' => 'bar'])->create();

    expect($voucher->metadata)->toBeInstanceOf(ArrayObject::class);
    expect($voucher->metadata['foo'])->toBe('bar');
    // Array access, property access and helpers are all available.
    expect($voucher->metadata->foo)->toBe('bar');
    expect($voucher->metadata->toArray())->toBe(['foo' => 'bar']);
    expect($voucher->metadata->collect())->toBeInstanceOf(Collection::class);
    expect(isset($voucher->metadata['foo']))->toBeTrue();
    expect(isset($voucher->metadata['nope']))->toBeFalse();
});

/**
 * Test metadata stays countable and iterable.
 */
test('metadata is countable and iterable', function () {
    $voucher = (new Vouchers())->withMetadata(['a' => 1, 'b' => 2])->create();

    expect(count($voucher->metadata))->toBe(2);

    $seen = [];
    foreach ($voucher->metadata as $key => $value) {
        $seen[$key] = $value;
    }

    expect($seen)->toBe(['a' => 1, 'b' => 2]);
});

/**
 * Test metadata can be mutated in place and persists.
 */
test('metadata can be mutated in place', function () {
    $voucher = (new Vouchers())->withMetadata(['amount' => 3])->create();

    $voucher->metadata['amount'] = 2;
    $voucher->metadata['extra'] = 'added';
    $voucher->save();

    expect($voucher->fresh()->metadata->toArray())->toBe(['amount' => 2, 'extra' => 'added']);
});

/**
 * Test removing a metadata key in place.
 */
test('metadata keys can be removed in place', function () {
    $voucher = (new Vouchers())->withMetadata(['foo' => 'bar', 'baz' => 'boom'])->create();

    unset($voucher->metadata['foo']);
    $voucher->save();

    expect($voucher->fresh()->metadata->toArray())->toBe(['baz' => 'boom']);
});

/**
 * Test nested metadata can be mutated in place.
 *
 * Chained array access writes through to the underlying array.
 */
test('nested metadata can be mutated in place', function () {
    $voucher = (new Vouchers())
        ->withMetadata(['nested' => ['foo' => 'bar'], 'counts' => ['amount' => 5]])
        ->create()
    ;

    $voucher->metadata['nested']['foo'] = 'changed';
    $voucher->metadata['counts']['amount']--;
    $voucher->save();

    expect($voucher->fresh()->metadata->toArray())->toBe([
        'nested' => ['foo' => 'changed'],
        'counts' => ['amount' => 4],
    ]);
});

/**
 * Test a nested array assigned to a variable is a copy.
 *
 * Normal PHP array value semantics rather than anything specific to the cast.
 */
test('nested metadata assigned to a variable is a copy', function () {
    $voucher = (new Vouchers())->withMetadata(['nested' => ['foo' => 'bar']])->create();

    $nested = $voucher->metadata['nested'];
    $nested['foo'] = 'changed';
    $voucher->save();

    // Only the copy was modified.
    expect($nested)->toBe(['foo' => 'changed']);
    expect($voucher->fresh()->metadata['nested'])->toBe(['foo' => 'bar']);

    // Assigning it back persists the change.
    $voucher->metadata['nested'] = $nested;
    $voucher->save();

    expect($voucher->fresh()->metadata['nested'])->toBe(['foo' => 'changed']);
});

/**
 * Test assigning a plain array replaces the metadata.
 */
test('metadata can be replaced with an array', function () {
    $voucher = (new Vouchers())->withMetadata(['foo' => 'bar'])->create();

    $voucher->metadata = ['baz' => 'boom'];
    $voucher->save();

    expect($voucher->fresh()->metadata->toArray())->toBe(['baz' => 'boom']);
});

/**
 * Test null metadata stays null in the database.
 */
test('null metadata is stored as null', function () {
    $voucher = (new Vouchers())->create();

    expect($voucher->metadata)->toBeNull();
    expect(DB::table($voucher->getTable())->where('id', $voucher->getKey())->value('metadata'))->toBeNull();
    expect(Voucher::whereNull('metadata')->count())->toBe(1);

    // Explicitly nulling existing metadata also clears the column.
    $other = (new Vouchers())->withMetadata(['foo' => 'bar'])->create();
    $other->metadata = null;
    $other->save();

    expect(DB::table($other->getTable())->where('id', $other->getKey())->value('metadata'))->toBeNull();
    expect(Voucher::whereNull('metadata')->count())->toBe(2);
});

/**
 * Test JSON path queries still work against metadata.
 */
test('metadata json path queries', function () {
    (new Vouchers())->withMetadata(['foo' => 'bar'])->create();
    (new Vouchers())->withMetadata(['foo' => 'baz'])->create();
    (new Vouchers())->withMetadata(['nested' => ['deep' => 'value']])->create();

    expect(Voucher::where('metadata->foo', 'bar')->count())->toBe(1);
    expect(Voucher::where('metadata->foo', 'baz')->count())->toBe(1);
    expect(Voucher::where('metadata->nested->deep', 'value')->count())->toBe(1);
    expect(Voucher::whereNotNull('metadata')->count())->toBe(3);
});

/**
 * Test metadata is serialized as an array.
 */
test('metadata serializes as an array', function () {
    $voucher = (new Vouchers())->withMetadata(['foo' => 'bar'])->create();

    expect($voucher->toArray()['metadata'])->toBe(['foo' => 'bar']);
    expect(json_decode($voucher->toJson(), true)['metadata'])->toBe(['foo' => 'bar']);

    // Null metadata serializes as null rather than an empty array.
    expect((new Vouchers())->create()->toArray()['metadata'])->toBeNull();
});
