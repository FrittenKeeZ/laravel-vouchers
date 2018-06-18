<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers;

class Vouchers
{
    /**
     * Voucher config.
     *
     * @var \FrittenKeeZ\Vouchers\Config
     */
    protected $config;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Get current voucher config.
     *
     * @return \FrittenKeeZ\Vouchers\Config
     */
    public function getConfig(): Config
    {
        return clone $this->config;
    }

    /**
     * Create an amount of vouchers.
     *
     * Defaults to a single voucher if amount is absent.
     *
     * @param  int  $amount
     * @return object|array
     */
    public function create(int $amount = 1)
    {
        if ($amount < 1) {
            return [];
        }

        $model = Config::model('voucher');
        $vouchers = [];
        foreach ($this->batch($amount) as $code) {
            $vouchers[] = $model::create(compact('code'));
        }

        $this->reset();

        return $amount === 1 ? reset($vouchers) : $vouchers;
    }

    /**
     * Generate a batch a codes, using the mask and character list from the config.
     *
     * Codes are checked against the database to ensure uniqueness.
     *
     * @param  int  $amount
     * @return string[]|array
     */
    public function batch(int $amount): array
    {
        if ($amount < 1) {
            return [];
        }

        $codes = [];
        for ($i = 0; $i < $amount; $i++) {
            do {
                $code = $this->generate();
            } while ($this->exists($code, $codes));

            $codes[] = $code;
        }

        return $codes;
    }

    /**
     * Generate a random code in the given mask format limited to the provided character list.
     *
     * All asterisks (*) in the mask will be replaced by a random character.
     * If no mask or character list is provided, defaults will be used from config.
     *
     * @param  string|null  $mask
     * @param  string|null  $characters
     * @return string
     */
    public function generate(string $mask = null, string $characters = null): string
    {
        $mask = $mask ?: $this->config->getMask();
        $characters = $characters ?: $this->config->getCharacters();

        $code = preg_replace_callback('/\*/', function (array $matches) use ($characters) {
            return $characters[random_int(0, mb_strlen($characters) - 1)];
        }, $mask);

        return $this->wrap(
            $code,
            $this->config->getPrefix(),
            $this->config->getSuffix(),
            $this->config->getSeparator()
        );
    }

    /**
     * Wrap string in prefix and suffix with separator.
     *
     * @param  string       $str
     * @param  string|null  $prefix
     * @param  string|null  $suffix
     * @param  string       $separator
     * @return string
     */
    public function wrap(string $str, ?string $prefix, ?string $suffix, string $separator): string
    {
        $prefix = empty($prefix) ? '' : $prefix . $separator;
        $suffix = empty($suffix) ? '' : $separator . $suffix;

        return $prefix . $str . $suffix;
    }

    /**
     * Whether the given code already exists.
     *
     * Optionally check a given list of codes, before checking the database.
     *
     * @param  string  $code
     * @param  array   $codes
     * @return bool
     */
    public function exists(string $code, array $codes = []): bool
    {
        $model = Config::model('voucher');

        return in_array($code, $codes) || $model::where('code', '=', $code)->exists();
    }

    /**
     * Reset voucher options.
     *
     * @return void
     */
    public function reset(): void
    {
        $this->config = new Config();
    }

    /**
     * Proxy 'with' and 'without' calls to config.
     *
     * Will trigger undefined method error for all invalid calls.
     *
     * @param  string  $name
     * @param  array   $args
     * @return $this
     */
    public function __call(string $name, array $args)
    {
        if (starts_with($name, 'with') && method_exists($this->config, $name)) {
            $this->config->$name(...$args);

            return $this;
        }

        trigger_error('Call to undefined method ' . static::class . '::' . $name . '()', E_USER_ERROR);
    }
}
