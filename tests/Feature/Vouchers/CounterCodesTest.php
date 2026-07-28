<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\CodeFormat;
use FrittenKeeZ\Vouchers\Exceptions;
use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Vouchers;
use Illuminate\Support\Str;

/**
 * Test counter derived from a code ending in a number.
 */
test('counter derived from trailing number', function () {
    $codes = (new Vouchers())->withCode('FIXED2025')->batch(3);

    expect($codes)->toBe(['FIXED2025', 'FIXED2026', 'FIXED2027']);
});

/**
 * Test explicit counter yields the same incrementation.
 */
test('explicit counter matches derived counter', function () {
    $codes = (new Vouchers())->withCode('FIXED')->withCounter(2025)->batch(3);

    expect($codes)->toBe(['FIXED2025', 'FIXED2026', 'FIXED2027']);
});

/**
 * Test counter is not applied for a single voucher unless explicit.
 */
test('counter only applies to single voucher when explicit', function () {
    // No explicit counter - static code is left alone.
    expect((new Vouchers())->withCode('FIXED2025')->batch(1))->toBe(['FIXED2025']);
    expect((new Vouchers())->withCode('FIXED')->batch(1))->toBe(['FIXED']);
    // Explicit counter is always honoured.
    expect((new Vouchers())->withCode('FIXED')->withCounter(2025)->batch(1))->toBe(['FIXED2025']);
});

/**
 * Test code without trailing number starts at one.
 */
test('counter without trailing number starts at one', function () {
    $codes = (new Vouchers())->withCode('FIXED')->batch(3);

    expect($codes)->toBe(['FIXED1', 'FIXED2', 'FIXED3']);
});

/**
 * Test explicit counter is appended to the full code.
 */
test('explicit counter is appended to full code', function () {
    $codes = (new Vouchers())->withCode('FIXED2025')->withCounter(1)->batch(2);

    expect($codes)->toBe(['FIXED20251', 'FIXED20252']);
});

/**
 * Test counter step - padding is calculated from the highest value of the batch.
 */
test('counter step', function () {
    $codes = (new Vouchers())->withCode('FIXED')->withCounter(1)->withCounterStep(10)->batch(3);

    expect($codes)->toBe(['FIXED01', 'FIXED11', 'FIXED21']);
    // Without padding the codes are left ragged.
    $codes = (new Vouchers())
        ->withCode('FIXED')
        ->withCounter(1)
        ->withCounterStep(10)
        ->withoutCounterPadding()
        ->batch(3)
    ;

    expect($codes)->toBe(['FIXED1', 'FIXED11', 'FIXED21']);
});

/**
 * Test padding is calculated from the amount when the code has no trailing number.
 */
test('counter padding calculated from amount', function () {
    $codes = (new Vouchers())->withCode('FIXED')->batch(100);

    expect($codes[0])->toBe('FIXED001');
    expect($codes[8])->toBe('FIXED009');
    expect($codes[9])->toBe('FIXED010');
    expect($codes[99])->toBe('FIXED100');
    // Every code is the same width.
    expect(array_unique(array_map('strlen', $codes)))->toHaveCount(1);
});

/**
 * Test calculated padding is an exact fit for the highest counter value.
 */
test('calculated padding is an exact fit', function () {
    // Highest value is 1 + 1 * 8 = 9, so a single digit suffices.
    expect((new Vouchers())->withCode('FIXED')->batch(9))->toBe([
        'FIXED1', 'FIXED2', 'FIXED3', 'FIXED4', 'FIXED5', 'FIXED6', 'FIXED7', 'FIXED8', 'FIXED9',
    ]);
    // One more tips it over to two digits.
    expect((new Vouchers())->withCode('FIXED')->batch(10))->toBe([
        'FIXED01', 'FIXED02', 'FIXED03', 'FIXED04', 'FIXED05',
        'FIXED06', 'FIXED07', 'FIXED08', 'FIXED09', 'FIXED10',
    ]);
});

/**
 * Test skipping existing codes can exceed the exact fit padding.
 */
test('skipped codes can exceed calculated padding', function () {
    Voucher::create(['code' => 'FIXED5']);

    // Padding fits 1-9, but skipping FIXED5 pushes the last code to 10.
    $codes = (new Vouchers())->withCode('FIXED')->batch(9);

    expect($codes)->toBe([
        'FIXED1', 'FIXED2', 'FIXED3', 'FIXED4', 'FIXED6', 'FIXED7', 'FIXED8', 'FIXED9', 'FIXED10',
    ]);
});

/**
 * Test withoutCounterPadding() disables padding entirely.
 */
test('without counter padding', function () {
    $codes = (new Vouchers())->withCode('FIXED')->withoutCounterPadding()->batch(100);

    expect($codes[0])->toBe('FIXED1');
    expect($codes[99])->toBe('FIXED100');
    // Padding inherited from the code is disabled too.
    expect((new Vouchers())->withCode('FIXED0025')->withoutCounterPadding()->batch(2))
        ->toBe(['FIXED25', 'FIXED26'])
    ;
});

/**
 * Test padding inherited from the code takes precedence over the calculated padding.
 */
test('code padding takes precedence over calculated padding', function () {
    // Calculated padding would be 3 for this amount, but the code dictates 4.
    $codes = (new Vouchers())->withCode('FIXED0025')->batch(100);

    expect($codes[0])->toBe('FIXED0025');
    expect($codes[99])->toBe('FIXED0124');
    expect(array_unique(array_map('strlen', $codes)))->toHaveCount(1);
});

/**
 * Test counter step must be a positive integer greater than zero.
 */
test('counter step must be positive', function (int $step) {
    (new Vouchers())->withCounterStep($step);
})->throws(InvalidArgumentException::class)->with([[0], [-1], [-10]]);

/**
 * Test counter separator.
 */
test('counter separator', function () {
    $codes = (new Vouchers())->withCode('FIXED')->withCounter(1)->withCounterSeparator('-')->batch(2);

    expect($codes)->toBe(['FIXED-1', 'FIXED-2']);
});

/**
 * Test padding is inherited from the trailing digits of the code.
 */
test('counter padding inherited from code', function () {
    $codes = (new Vouchers())->withCode('FIXED0025')->batch(3);

    expect($codes)->toBe(['FIXED0025', 'FIXED0026', 'FIXED0027']);
});

/**
 * Test explicit padding takes precedence over inherited padding.
 */
test('explicit padding takes precedence', function () {
    $codes = (new Vouchers())->withCode('FIXED0025')->withCounterPadding(6)->batch(2);

    expect($codes)->toBe(['FIXED000025', 'FIXED000026']);
});

/**
 * Test explicit counter does not inherit padding.
 */
test('explicit counter does not inherit padding', function () {
    $codes = (new Vouchers())->withCode('FIXED')->withCounter(25)->batch(2);

    expect($codes)->toBe(['FIXED25', 'FIXED26']);
});

/**
 * Test custom padding character.
 */
test('counter padding character', function () {
    $codes = (new Vouchers())->withCode('FIXED')->withCounter(1)->withCounterPadding(3, 'x')->batch(2);

    expect($codes)->toBe(['FIXEDxx1', 'FIXEDxx2']);
});

/**
 * Test counter skips codes which already exist.
 */
test('counter skips existing codes', function () {
    Voucher::create(['code' => 'FIXED2026']);

    $codes = (new Vouchers())->withCode('FIXED2025')->batch(2);

    expect($codes)->toBe(['FIXED2025', 'FIXED2027']);
});

/**
 * Test custom code formatter receives a CodeFormat instance.
 */
test('custom code formatter', function () {
    $seen = [];
    $codes = (new Vouchers())
        ->withCode('FIXED')
        ->withCounter(5)
        ->withCounterSeparator('-')
        ->withCounterPadding(3)
        ->withCodeFormatter(function (CodeFormat $format) use (&$seen) {
            $seen[] = $format;

            return \sprintf('%s%s%s', strtolower($format->code), $format->separator, $format->paddedCounter());
        })
        ->batch(2)
    ;

    expect($codes)->toBe(['fixed-005', 'fixed-006']);
    // Verify the formatter received all the parts.
    expect($seen[0]->code)->toBe('FIXED');
    expect($seen[0]->counter)->toBe(5);
    expect($seen[0]->separator)->toBe('-');
    expect($seen[0]->padding)->toBe(3);
    expect($seen[0]->pad)->toBe('0');
    // Casting to string yields the default formatting.
    expect((string) $seen[0])->toBe('FIXED-005');
});

/**
 * Test counter options throw when combined with a mask containing asterisks.
 */
test('counter with mask throws', function (Closure $callback) {
    $callback(new Vouchers());
})->throws(Exceptions\CounterException::class)->with([
    'counter'   => [fn (Vouchers $v) => $v->withMask('FOO-****')->withCounter(1)->batch(2)],
    'step'      => [fn (Vouchers $v) => $v->withMask('FOO-****')->withCounterStep(2)->batch(2)],
    'separator' => [fn (Vouchers $v) => $v->withMask('FOO-****')->withCounterSeparator('-')->batch(2)],
    'padding'   => [fn (Vouchers $v) => $v->withMask('FOO-****')->withCounterPadding(3)->batch(2)],
    'nopadding' => [fn (Vouchers $v) => $v->withMask('FOO-****')->withoutCounterPadding()->batch(2)],
    'formatter' => [fn (Vouchers $v) => $v->withMask('FOO-****')->withCodeFormatter(fn () => 'x')->batch(2)],
]);

/**
 * Test counter options are reset along with the other dynamic options.
 */
test('counter options are reset', function () {
    $vouchers = new Vouchers();
    $vouchers->withCode('FIXED')->withCounter(5)->withCounterStep(3)->withCounterSeparator('-')->withCounterPadding(4);

    expect($vouchers->getConfig()->hasCounterOptions())->toBeTrue();

    $vouchers->reset();

    expect($vouchers->getConfig()->hasCounterOptions())->toBeFalse();
    expect($vouchers->getCounter())->toBeNull();
    expect($vouchers->getCounterStep())->toBe(1);
    expect($vouchers->getCounterSeparator())->toBe('');
    expect($vouchers->getCounterPadding())->toBeNull();
    expect($vouchers->getCode())->toBeNull();
});

/**
 * Test padding option resetting.
 */
test('counter padding can be reset to calculated', function () {
    $vouchers = new Vouchers();

    // Explicit padding.
    expect($vouchers->withCounterPadding(6)->getCounterPadding())->toBe(6);
    // Disabled padding is an explicit zero, not an absent value.
    expect($vouchers->withoutCounterPadding()->getCounterPadding())->toBe(0);
    // Null resets back to the calculated padding.
    expect($vouchers->withCounterPadding(null)->getCounterPadding())->toBeNull();
    // Negative lengths are clamped rather than treated as absent.
    expect($vouchers->withCounterPadding(-5)->getCounterPadding())->toBe(0);
});

/**
 * Test creating actual vouchers with counter based codes.
 */
test('creating vouchers with counter codes', function () {
    $vouchers = (new Vouchers())->withCode('FIXED2025')->create(3);

    expect($vouchers)->toHaveCount(3);
    expect(array_map(fn (Voucher $voucher) => $voucher->code, $vouchers))
        ->toBe(['FIXED2025', 'FIXED2026', 'FIXED2027'])
    ;
});

/**
 * Test the combined example documented in the README.
 */
test('readme combined options example', function () {
    $codes = (new Vouchers())
        ->withCode('FIXED')
        ->withCounter(1)
        ->withCounterStep(10)
        ->withCounterSeparator('-')
        ->withCounterPadding(4)
        ->batch(3)
    ;

    expect($codes)->toBe(['FIXED-0001', 'FIXED-0011', 'FIXED-0021']);
});

/**
 * Test the formatter example documented in the README.
 */
test('readme formatter example', function () {
    $codes = (new Vouchers())
        ->withCode('FIXED')
        ->withCounter(5)
        ->withCounterPadding(3)
        ->withCodeFormatter(fn (CodeFormat $format) => \sprintf(
            '%s/%s',
            strtolower($format->code),
            $format->paddedCounter()
        ))
        ->batch(2)
    ;

    expect($codes)->toBe(['fixed/005', 'fixed/006']);
});

/**
 * Test the hexadecimal formatter example documented in the README.
 *
 * Uses the raw counter for the conversion, while re-using the configured padding.
 */
test('readme hexadecimal formatter example', function () {
    $codes = (new Vouchers())
        ->withCode('GIFT')
        ->withCounter(250)
        ->withCounterSeparator('-')
        ->withCounterPadding(4)
        ->withCodeFormatter(fn (CodeFormat $format) => \sprintf(
            '%s%s%s',
            $format->code,
            $format->separator,
            Str::padLeft(strtoupper(dechex($format->counter)), $format->padding, $format->pad)
        ))
        ->batch(3)
    ;

    expect($codes)->toBe(['GIFT-00FA', 'GIFT-00FB', 'GIFT-00FC']);
});

/**
 * Test repeatedly creating a single voucher from the same base keeps advancing.
 *
 * The attempt bound scales with existing vouchers, not the requested amount, so this
 * must keep working no matter how many vouchers already share the base code.
 */
test('counter keeps advancing across repeated single creates', function () {
    $codes = [];
    for ($i = 0; $i < 15; $i++) {
        $codes[] = (new Vouchers())->withCode('GIFT')->withCounter(2025)->create()->code;
    }

    expect($codes)->toBe(array_map(fn (int $n) => 'GIFT' . $n, range(2025, 2039)));
});

/**
 * Test a formatter ignoring the counter can only produce a single unique code.
 */
test('constant formatter produces a single code', function () {
    $constant = fn (CodeFormat $format) => 'CONSTANT';

    // A single voucher succeeds, since the code is not taken yet.
    expect((new Vouchers())->withCode('FIXED')->withCodeFormatter($constant)->withCounter(1)->batch(1))
        ->toBe(['CONSTANT'])
    ;
});

/**
 * Test a formatter ignoring the counter is caught as a runaway loop.
 */
test('constant formatter throws infinite loop', function (bool $existing) {
    $constant = fn (CodeFormat $format) => 'CONSTANT';

    if ($existing) {
        // The code is already taken, so even a single voucher cannot be created.
        Voucher::create(['code' => 'CONSTANT']);

        (new Vouchers())->withCode('FIXED')->withCodeFormatter($constant)->withCounter(1)->batch(1);
    } else {
        // Nothing exists, but the second code collides with the first in the same batch.
        (new Vouchers())->withCode('FIXED')->withCodeFormatter($constant)->withCounter(1)->batch(2);
    }
})->throws(Exceptions\InfiniteLoopException::class)->with([
    'existing code' => [true],
    'batch of two'  => [false],
]);

/**
 * Test the constant formatter example documented in the README.
 */
test('readme constant formatter example', function () {
    (new Vouchers())
        ->withCode('FIXED')
        ->withCodeFormatter(fn (CodeFormat $format) => 'CONSTANT')
        ->batch(2)
    ;
})->throws(Exceptions\InfiniteLoopException::class);

/**
 * Test prefix and suffix still wrap counter based codes when re-enabled.
 */
test('counter codes respect prefix and suffix', function () {
    $codes = (new Vouchers())
        ->withCode('FIXED')
        ->withCounter(1)
        ->withPrefix('PRE')
        ->withSuffix('SUF')
        ->withSeparator('-')
        ->batch(2)
    ;

    expect($codes)->toBe(['PRE-FIXED1-SUF', 'PRE-FIXED2-SUF']);
});
