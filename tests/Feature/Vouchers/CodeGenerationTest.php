<?php

declare(strict_types=1);

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
test(
    'code generation',
    function (?string $mask, ?string $characters, ?string $prefix, ?string $suffix, ?string $separator) {
        $vouchers = new Vouchers();
        // Set mask, characters, prefix, suffix and separator.
        $vouchers
            ->withMask($mask)
            ->withCharacters($characters)
            ->withPrefix($prefix)
            ->withSuffix($suffix)
            ->withSeparator($separator)
        ;

        // Check vouchers proxy call to config.
        expect($vouchers->getMask())->toBe($mask ?: $vouchers->getConfig()->getMask());
        expect($vouchers->getCharacters())->toBe($characters ?: $vouchers->getConfig()->getCharacters());
        expect($vouchers->getPrefix())->toBe($prefix ?: $vouchers->getConfig()->getPrefix());
        expect($vouchers->getSuffix())->toBe($suffix ?: $vouchers->getConfig()->getSuffix());
        expect($vouchers->getSeparator())->toBe($separator ?: $vouchers->getConfig()->getSeparator());

        // Grab validation regex.
        $regex = _generate_code_validation_regex(
            $vouchers->getMask(),
            $vouchers->getCharacters(),
            $vouchers->getPrefix(),
            $vouchers->getSuffix(),
            $vouchers->getSeparator()
        );

        // Generate a code and check if it matches the regex.
        expect($vouchers->generate())->toMatch($regex);
    }
)->with([
    [null, null, null, null, null],
    ['****', '0123456789', null, null, '-'],
    ['****', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'PRE', null, '_'],
    ['****', 'abcdefghijklmnopqrstuvwxyz', null, 'SUF', '_'],
    ['****-****', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz', 'PRE', 'SUF', '_'],
    ['****-****', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz', null, null, '-'],
]);

/**
 * Test batch generation.
 */
test('batch generation', function () {
    $vouchers = new Vouchers();

    // Grab validation regex.
    $regex = _generate_code_validation_regex(
        $vouchers->getMask(),
        $vouchers->getCharacters(),
        $vouchers->getPrefix(),
        $vouchers->getSuffix(),
        $vouchers->getSeparator()
    );

    // Test batch operation.
    $batch = $vouchers->batch(5);
    expect($batch)->toHaveCount(5);
    foreach ($batch as $code) {
        expect($code)->toMatch($regex);
    }

    // Test negative batch amount.
    expect($vouchers->batch(-5))->toBeEmpty();
});
