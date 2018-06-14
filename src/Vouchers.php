<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers;

class Vouchers
{
    /**
     * Voucher options.
     *
     * @var array
     */
    protected $options = [];

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

        $model = Helpers::model('voucher');
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

        $characters = config('vouchers.characters');
        $mask = config('vouchers.mask');
        $codes = [];
        for ($i = 0; $i < $amount; $i++) {
            do {
                $code = $this->generate($mask, $characters);
            } while ($this->exists($code, $codes));

            $codes[] = $code;
        }

        return $codes;
    }

    /**
     * Generate a random code in the given mask format limited to the provided character list.
     *
     * All asterisks (*) in the mask will be replaced by a random character.
     *
     * @param  string  $mask
     * @param  string  $characters
     * @return string
     */
    public function generate(string $mask, string $characters): string
    {
        if (empty($mask) || empty($characters)) {
            return $mask;
        }

        return preg_replace_callback('/\*/', function (array $matches) use ($characters) {
            return $characters[random_int(0, mb_strlen($characters) - 1)];
        }, $mask);
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
        $model = Helpers::model('voucher');

        return in_array($code, $codes) || $model::where('code', '=', $code)->exists();
    }

    /**
     * Reset voucher options.
     *
     * @return void
     */
    public function reset(): void
    {
        $this->options = [];
    }
}
