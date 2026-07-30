<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Vouchers;
use Illuminate\Support\Facades\DB;

/**
 * Count voucher lookup queries performed while running the given callback.
 *
 * @return array{0: mixed, 1: int}
 */
function voucherLookups(Closure $callback): array
{
    $queries = 0;
    DB::listen(function ($query) use (&$queries) {
        if (str_starts_with($query->sql, 'select') && str_contains($query->sql, 'vouchers')) {
            $queries++;
        }
    });

    return [$callback(), $queries];
}

/**
 * Test random codes are checked in a single query regardless of amount.
 */
test('random batch uses a single query', function (int $amount) {
    [$codes, $queries] = voucherLookups(fn () => (new Vouchers())->batch($amount));

    expect($codes)->toHaveCount($amount);
    expect($queries)->toBe(1);
})->with([1, 2, 10, 100, 500]);

/**
 * Test large batches are chunked rather than sent as one huge query.
 */
test('large random batch is chunked', function () {
    [$codes, $queries] = voucherLookups(fn () => (new Vouchers())->batch(1200));

    expect($codes)->toHaveCount(1200);
    expect(array_unique($codes))->toHaveCount(1200);
    // Three chunks of 500, rather than 1200 individual lookups.
    expect($queries)->toBe(3);
});

/**
 * Test counter codes are checked in a single query regardless of amount.
 */
test('counter batch uses a single query', function (int $amount) {
    [$codes, $queries] = voucherLookups(fn () => (new Vouchers())->withCode('FIXED')->batch($amount));

    expect($codes)->toHaveCount($amount);
    expect($queries)->toBe(1);
})->with([2, 10, 100]);

/**
 * Test skipping existing codes stays well below one query per code.
 */
test('counter batch skipping existing codes stays cheap', function () {
    foreach (range(1, 20) as $i) {
        Voucher::create(['code' => "TAKEN{$i}"]);
    }

    [$codes, $queries] = voucherLookups(fn () => (new Vouchers())->withCode('TAKEN')->batch(10));

    // TAKEN10 collides with the existing code, so the counter runs past 11-20.
    expect($codes)->toBe([
        'TAKEN01', 'TAKEN02', 'TAKEN03', 'TAKEN04', 'TAKEN05',
        'TAKEN06', 'TAKEN07', 'TAKEN08', 'TAKEN09', 'TAKEN21',
    ]);
    // Pooling overshoots by the amount taken, so this must stay far below the 12 collisions.
    expect($queries)->toBeLessThan(8);
});

/**
 * Test existing codes are still never handed out.
 */
test('batch never returns an existing code', function () {
    $existing = (new Vouchers())->create(50);
    $taken = array_map(fn (Voucher $voucher) => $voucher->code, $existing);

    $codes = (new Vouchers())->batch(50);

    expect(array_intersect($codes, $taken))->toBeEmpty();
    expect(array_unique($codes))->toHaveCount(50);
});
