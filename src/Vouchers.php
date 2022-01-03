<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers;

use Closure;
use FrittenKeeZ\Vouchers\Exceptions\VoucherAlreadyRedeemedException;
use FrittenKeeZ\Vouchers\Exceptions\VoucherNotFoundException;
use FrittenKeeZ\Vouchers\Models\Redeemer;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Vouchers
{
    /**
     * Voucher config.
     *
     * @var \FrittenKeeZ\Vouchers\Config
     */
    protected Config $config;

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
     * Proxy 'get', 'with' and 'without' calls to config.
     *
     * Will trigger undefined method error for all invalid calls.
     *
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        if (method_exists($this->config, $name)) {
            if (Str::startsWith($name, 'get')) {
                return $this->config->{$name}(...$args);
            }

            if (Str::startsWith($name, 'with')) {
                $this->config->{$name}(...$args);

                return $this;
            }
        }

        trigger_error('Call to undefined method ' . static::class . '::' . $name . '()', \E_USER_ERROR);
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
     * @param int $amount
     *
     * @return object|array
     */
    public function create(int $amount = 1)
    {
        if ($amount < 1) {
            return [];
        }

        $options = [
            'metadata'   => $this->config->getMetadata(),
            'starts_at'  => $this->config->getStartTime(),
            'expires_at' => $this->config->getExpireTime(),
        ];
        $owner = $this->config->getOwner();
        $entities = $this->config->getEntities();
        $vouchers = [];
        // Ensure nothing is committed to the database if anything fails.
        DB::transaction(function () use ($amount, $options, $owner, $entities, &$vouchers) {
            foreach ($this->batch($amount) as $code) {
                $voucher = $this->vouchers()->create(compact('code') + $options);
                if (!empty($owner)) {
                    $voucher->owner()->associate($owner)->save();
                }
                if (!empty($entities)) {
                    $voucher->addEntities(...$entities);
                }

                $vouchers[] = $voucher;
            }
        });

        $this->reset();

        return $amount === 1 ? reset($vouchers) : $vouchers;
    }

    /**
     * Redeem a voucher code.
     *
     * Returns whether redemption was successful.
     *
     * @param string                              $code
     * @param \Illuminate\Database\Eloquent\Model $entity   Redeemer entity.
     * @param array                               $metadata Additional metadata for redeemer.
     *
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherNotFoundException
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherAlreadyRedeemedException
     *
     * @return bool
     */
    public function redeem(string $code, Model $entity, array $metadata = []): bool
    {
        $voucher = $this->vouchers()->code($code)->first();
        if ($voucher === null) {
            throw new VoucherNotFoundException();
        }
        if (!$voucher->isRedeemable()) {
            throw new VoucherAlreadyRedeemedException();
        }

        $redeemer = $this->redeemers();
        if (!empty($metadata)) {
            $redeemer->metadata = $metadata;
        }
        $redeemer->redeemer()->associate($entity);
        $success = false;
        // Ensure nothing is committed to the database if anything fails.
        DB::transaction(function () use ($voucher, $redeemer, &$success) {
            $success = $voucher->redeem($redeemer);
        });

        return $success;
    }

    /**
     * Whether a voucher code is redeemable.
     *
     * @param string        $code
     * @param \Closure|null $callback
     *
     * @return bool
     */
    public function redeemable(string $code, ?Closure $callback = null): bool
    {
        $voucher = $this->vouchers()->code($code)->first();

        return $voucher !== null && $voucher->isRedeemable() && ($callback === null || $callback($voucher));
    }

    /**
     * Generate a batch a codes, using the mask and character list from the config.
     *
     * Codes are checked against the database to ensure uniqueness.
     *
     * @param int $amount
     *
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
     * @param string|null $mask
     * @param string|null $characters
     *
     * @return string
     */
    public function generate(?string $mask = null, ?string $characters = null): string
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
     * @param string      $str
     * @param string|null $prefix
     * @param string|null $suffix
     * @param string      $separator
     *
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
     * @param string $code
     * @param array  $codes
     *
     * @return bool
     */
    public function exists(string $code, array $codes = []): bool
    {
        return \in_array($code, $codes) || $this->vouchers()->code($code)->exists();
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
     * Convenience method for interacting with Redeemer model.
     *
     * @return \FrittenKeeZ\Vouchers\Models\Redeemer
     */
    protected function redeemers(): Redeemer
    {
        return Config::model('redeemer')::make();
    }

    /**
     * Convenience method for interacting with Voucher model.
     *
     * @return \FrittenKeeZ\Vouchers\Models\Voucher
     */
    protected function vouchers(): Voucher
    {
        return Config::model('voucher')::make();
    }
}
