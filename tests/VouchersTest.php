<?php

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Vouchers;

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

        // Test single generation.
        $this->assertRegExp($regex, $vouchers->generate($mask, $characters));

        // Test batch operation.
        foreach ($vouchers->batch(10) as $code) {
            $this->assertRegExp($regex, $code);
        }
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
    public function testStringWrapping(string $str, ?string $prefix, ?string $suffix, string $separator, string $expected): void
    {
        $this->assertSame($expected, (new Vouchers)->wrap($str, $prefix, $suffix, $separator));
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
