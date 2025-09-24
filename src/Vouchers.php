<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers;

use Closure;
use ErrorException;
use FrittenKeeZ\Vouchers\Models\Redeemer;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @method array           getOptions()
 * @method string          getCharacters()
 * @method self            withCharacters(?string $characters)
 * @method string          getMask()
 * @method self            withMask(?string $mask)
 * @method ?string         getPrefix()
 * @method self            withPrefix(?string $prefix)
 * @method self            withoutPrefix()
 * @method ?string         getSuffix()
 * @method self            withSuffix(?string $suffix)
 * @method self            withoutSuffix()
 * @method string          getSeparator()
 * @method self            withSeparator(?string $separator)
 * @method self            withoutSeparator()
 * @method self            withCode(string $code)
 * @method ?array          getMetadata()
 * @method self            withMetadata(?array $metadata)
 * @method ?\Carbon\Carbon getStartTime()
 * @method self            withStartTime(?\DateTime $timestamp)
 * @method self            withStartTimeIn(?\DateInterval $interval)
 * @method self            withStartDate(?\DateTime $timestamp)
 * @method self            withStartDateIn(?\DateInterval $interval)
 * @method ?\Carbon\Carbon getExpireTime()
 * @method self            withExpireTime(?\DateTime $timestamp)
 * @method self            withExpireTimeIn(?\DateInterval $interval)
 * @method self            withExpireDate(?\DateTime $timestamp)
 * @method self            withExpireDateIn(?\DateInterval $interval)
 * @method array|Model[]   getEntities()
 * @method self            withEntities(iterable|Model $entities = [], Model ...$remaining)
 * @method ?Model          getOwner()
 * @method self            withOwner(?Model $owner)
 *
 * @see \FrittenKeeZ\Vouchers\Config
 */
class Vouchers
{
    /**
     * Voucher config.
     */
    protected Config $config;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Proxy 'get', 'with' and 'without' calls to config.
     *
     * Will trigger undefined method error for all invalid calls.
     */
    public function __call(string $name, array $args): mixed
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

        throw new ErrorException('Call to undefined method ' . static::class . "::{$name}()", \E_USER_ERROR);
    }

    /**
     * Get current voucher config.
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
     * @throws \FrittenKeeZ\Vouchers\Exceptions\InfiniteLoopException
     */
    public function create(int $amount = 1): array|object
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
     * Returns whether redeeming was successful.
     *
     * @param \Illuminate\Database\Eloquent\Model $entity   Redeemer entity.
     * @param array                               $metadata Additional metadata for redeemer.
     *
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherNotFoundException
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherRedeemedException
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherUnstartedException
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherExpiredException
     */
    public function redeem(string $code, Model $entity, array $metadata = []): bool
    {
        /** @var \FrittenKeeZ\Vouchers\Models\Voucher $voucher */
        $voucher = $this->vouchers()->code($code)->first();
        // If the voucher is null or not redeemable, throw an appropriate exception.
        if (!$voucher?->isRedeemable()) {
            match (true) {
                $voucher === null      => throw new Exceptions\VoucherNotFoundException(),
                $voucher->isRedeemed() => throw new Exceptions\VoucherRedeemedException(),
                !$voucher->isStarted() => throw new Exceptions\VoucherUnstartedException(),
                $voucher->isExpired()  => throw new Exceptions\VoucherExpiredException(),
            };
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
     * Unredeem a voucher code.
     *
     * Returns whether unredeeming was successful.
     *
     * @param \Illuminate\Database\Eloquent\Model|null             $entity   Redeemer entity.
     * @param \Closure(\Illuminate\Database\Eloquent\Builder)|null $callback Optional callback to filter redeemer query.
     *
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherNotFoundException
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherRedeemerNotFoundException
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherUnstartedException
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherExpiredException
     */
    public function unredeem(string $code, ?Model $entity = null, ?Closure $callback = null): bool
    {
        /** @var \FrittenKeeZ\Vouchers\Models\Voucher $voucher */
        $voucher = $this->vouchers()->code($code)->first();
        if ($voucher === null) {
            throw new Exceptions\VoucherNotFoundException();
        }
        /** @var \FrittenKeeZ\Vouchers\Models\Redeemer $redeemer */
        $redeemer = $voucher->redeemers()
            ->when($entity !== null, fn ($query) => $query->whereMorphedTo('redeemer', $entity))
            ->when($callback !== null, $callback)
            ->first()
        ;
        // If redeemer is not found or the voucher not unredeemable, throw an appropriate exception.
        if ($redeemer === null || !$voucher->isUnredeemable()) {
            match (true) {
                $redeemer === null     => throw new Exceptions\VoucherRedeemerNotFoundException(),
                !$voucher->isStarted() => throw new Exceptions\VoucherUnstartedException(),
                $voucher->isExpired()  => throw new Exceptions\VoucherExpiredException(),
            };
        }

        $success = false;
        // Ensure nothing is committed to the database if anything fails.
        DB::transaction(function () use ($voucher, $redeemer, &$success) {
            $success = $voucher->unredeem($redeemer);
        });

        return $success;
    }

    /**
     * Whether a voucher code is redeemable.
     *
     * @param \Closure(\FrittenKeeZ\Vouchers\Models\Voucher)|null $callback Optional callback to perform extra checks.
     */
    public function redeemable(string $code, ?Closure $callback = null): bool
    {
        /** @var \FrittenKeeZ\Vouchers\Models\Voucher $voucher */
        $voucher = $this->vouchers()->code($code)->first();

        return $voucher !== null && $voucher->isRedeemable() && ($callback === null || $callback($voucher));
    }

    /**
     * Whether a voucher code is unredeemable.
     *
     * @param \Closure(\FrittenKeeZ\Vouchers\Models\Voucher)|null $callback Optional callback to perform extra checks.
     */
    public function unredeemable(string $code, ?Closure $callback = null): bool
    {
        /** @var \FrittenKeeZ\Vouchers\Models\Voucher $voucher */
        $voucher = $this->vouchers()->code($code)->first();

        return $voucher !== null && $voucher->isUnredeemable() && ($callback === null || $callback($voucher));
    }

    /**
     * Generate a batch a codes, using the mask and character list from the config.
     *
     * Codes are checked against the database to ensure uniqueness.
     *
     * @throws \FrittenKeeZ\Vouchers\Exceptions\InfiniteLoopException
     *
     * @return array|string[]
     */
    public function batch(int $amount): array
    {
        if ($amount < 1) {
            return [];
        }

        $attempts = substr_count($this->config->getMask(), '*') * Str::length($this->config->getCharacters());
        $codes = [];
        for ($i = 0; $i < $amount; $i++) {
            $attempt = 0;
            do {
                $code = $this->generate();
                // Prevent infinite loop.
                if ($attempt++ > $attempts) {
                    throw new Exceptions\InfiniteLoopException();
                }
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
     */
    public function generate(?string $mask = null, ?string $characters = null): string
    {
        $mask = $mask ?: $this->config->getMask();
        $characters = $characters ?: $this->config->getCharacters();

        $code = preg_replace_callback('/\*/', fn () => $characters[random_int(0, Str::length($characters) - 1)], $mask);

        return $this->wrap(
            $code,
            $this->config->getPrefix(),
            $this->config->getSuffix(),
            $this->config->getSeparator()
        );
    }

    /**
     * Wrap string in prefix and suffix with separator.
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
     */
    public function exists(string $code, array $codes = []): bool
    {
        return \in_array($code, $codes, true) || $this->vouchers()->code($code)->exists();
    }

    /**
     * Reset voucher options.
     */
    public function reset(): void
    {
        $this->config = new Config();
    }

    /**
     * Convenience method for interacting with Redeemer model.
     */
    protected function redeemers(): Redeemer
    {
        return Config::model('redeemer')::make();
    }

    /**
     * Convenience method for interacting with Voucher model.
     */
    protected function vouchers(): Voucher
    {
        return Config::model('voucher')::make();
    }
}
