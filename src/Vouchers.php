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
 * @method ?string         getCode()
 * @method self            withCode(string $code)
 * @method ?int            getCounter()
 * @method self            withCounter(?int $start = 1)
 * @method int             getCounterStep()
 * @method self            withCounterStep(?int $step)
 * @method string          getCounterSeparator()
 * @method self            withCounterSeparator(?string $separator)
 * @method ?int            getCounterPadding()
 * @method string          getCounterPad()
 * @method self            withCounterPadding(?int $length, string $pad = '0')
 * @method self            withoutCounterPadding()
 * @method ?\Closure       getCodeFormatter()
 * @method self            withCodeFormatter(?\Closure $formatter)
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
                $voucher = $this->vouchers()->fill(compact('code') + $options);
                if (!empty($owner)) {
                    $voucher->owner()->associate($owner);
                }
                $voucher->save();
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
     * Redeem a voucher code or voucher instance.
     *
     * Returns whether redeeming was successful.
     *
     * @param \FrittenKeeZ\Vouchers\Models\Voucher|string $voucher  Voucher code or instance.
     * @param \Illuminate\Database\Eloquent\Model         $entity   Redeemer entity.
     * @param array                                       $metadata Additional metadata for redeemer.
     *
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherNotFoundException
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherRedeemedException
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherUnstartedException
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherExpiredException
     */
    public function redeem(string|Voucher $voucher, Model $entity, array $metadata = []): bool
    {
        $voucher = $this->resolveVoucher($voucher);
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
     * Unredeem a voucher code or voucher instance.
     *
     * Returns whether unredeeming was successful.
     *
     * @param \FrittenKeeZ\Vouchers\Models\Voucher|string          $voucher  Voucher code or instance.
     * @param \Illuminate\Database\Eloquent\Model|null             $entity   Redeemer entity.
     * @param \Closure(\Illuminate\Database\Eloquent\Builder)|null $callback Optional callback to filter redeemer query.
     *
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherNotFoundException
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherRedeemerNotFoundException
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherUnstartedException
     * @throws \FrittenKeeZ\Vouchers\Exceptions\VoucherExpiredException
     */
    public function unredeem(string|Voucher $voucher, ?Model $entity = null, ?Closure $callback = null): bool
    {
        $voucher = $this->resolveVoucher($voucher);
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
     * Whether a voucher code or voucher instance is redeemable.
     *
     * @param \FrittenKeeZ\Vouchers\Models\Voucher|string         $voucher  Voucher code or instance.
     * @param \Closure(\FrittenKeeZ\Vouchers\Models\Voucher)|null $callback Optional callback to perform extra checks.
     */
    public function redeemable(string|Voucher $voucher, ?Closure $callback = null): bool
    {
        $voucher = $this->resolveVoucher($voucher);

        return $voucher !== null && $voucher->isRedeemable() && ($callback === null || $callback($voucher));
    }

    /**
     * Whether a voucher code or voucher instance is unredeemable.
     *
     * @param \FrittenKeeZ\Vouchers\Models\Voucher|string         $voucher  Voucher code or instance.
     * @param \Closure(\FrittenKeeZ\Vouchers\Models\Voucher)|null $callback Optional callback to perform extra checks.
     */
    public function unredeemable(string|Voucher $voucher, ?Closure $callback = null): bool
    {
        $voucher = $this->resolveVoucher($voucher);

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

        $wildcards = substr_count($this->config->getMask(), '*');
        // Counter options only make sense for static codes.
        if ($wildcards > 0 && $this->config->hasCounterOptions()) {
            throw new Exceptions\CounterException();
        }
        // Counter handling applies to static codes, either with an explicit counter
        // or when creating more than one voucher from a code set with withCode().
        if ($wildcards === 0 && $this->shouldUseCounter($amount)) {
            return $this->counterBatch($amount);
        }

        $attempts = $wildcards * Str::length($this->config->getCharacters());
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

        $max = Str::length($characters) - 1;
        $code = preg_replace_callback('/\*/', fn () => $characters[random_int(0, $max)], $mask);

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
        return \in_array($code, $codes, true) || $this->vouchers()->withCode($code)->exists();
    }

    /**
     * Reset voucher options.
     */
    public function reset(): void
    {
        $this->config = new Config();
    }

    /**
     * Whether counter handling should be used for the given amount.
     *
     * An explicit counter is always honoured, whereas a counter derived from the code
     * only kicks in when creating more than one voucher from a static code.
     */
    protected function shouldUseCounter(int $amount): bool
    {
        return $this->config->getCounter() !== null
            || ($this->config->getCode() !== null && $amount > 1);
    }

    /**
     * Generate a batch of counter based codes for a static code.
     *
     * Codes are checked against the database to ensure uniqueness, advancing the
     * counter past any code which is already taken.
     *
     * @throws \FrittenKeeZ\Vouchers\Exceptions\InfiniteLoopException
     *
     * @return array|string[]
     */
    protected function counterBatch(int $amount): array
    {
        [$base, $counter, $padding] = $this->resolveCounter($amount);
        $step = $this->config->getCounterStep();
        $counterSeparator = $this->config->getCounterSeparator();
        $pad = $this->config->getCounterPad();
        $formatter = $this->config->getCodeFormatter();
        // Wrapping options are the same for every code.
        $prefix = $this->config->getPrefix();
        $suffix = $this->config->getSuffix();
        $separator = $this->config->getSeparator();
        // Allow the counter to skip codes without scanning indefinitely. Both the batch span
        // and the range the padding can represent are damped by their square root, so large
        // steps, amounts and paddings fail fast rather than hammering the database. The
        // padding contribution tops out at 100, and without padding a single spare attempt
        // is left per code.
        $attempts = (int) round(sqrt($step * $amount) + sqrt(10 ** min($padding, 4)));

        $codes = [];
        for ($i = 0; $i < $amount; $i++) {
            $attempt = 0;
            do {
                $format = new CodeFormat($base, $counter, $counterSeparator, $padding, $pad);
                $code = $this->wrap(
                    $formatter === null ? (string) $format : $formatter($format),
                    $prefix,
                    $suffix,
                    $separator
                );
                $counter += $step;
                // Prevent runaway loops, fx. from a formatter returning a constant code.
                if ($attempt++ > $attempts) {
                    throw new Exceptions\InfiniteLoopException();
                }
            } while ($this->exists($code, $codes));

            $codes[] = $code;
        }

        return $codes;
    }

    /**
     * Resolve the counter base code, start value and padding length.
     *
     * Padding precedence is withCounterPadding() and withoutCounterPadding(), then
     * inherited from the trailing digits of the code, then calculated from the batch.
     *
     * @return array{0: string, 1: int, 2: int}
     */
    protected function resolveCounter(int $amount): array
    {
        $code = $this->config->getMask();
        $counter = $this->config->getCounter();
        $padding = $this->config->getCounterPadding();
        $step = $this->config->getCounterStep();

        // An explicit counter is always appended to the full code.
        if ($counter !== null) {
            return [$code, $counter, $padding ?? $this->calculatePadding($counter, $step, $amount)];
        }

        // Otherwise derive the counter from a trailing number, inheriting its padding.
        if (preg_match('/^(.*?)(\d+)$/', $code, $matches) === 1) {
            return [$matches[1], (int) $matches[2], $padding ?? \strlen($matches[2])];
        }

        return [$code, 1, $padding ?? $this->calculatePadding(1, $step, $amount)];
    }

    /**
     * Calculate the padding length needed to fit the highest counter value of a batch.
     *
     * Note that skipping existing codes can push the counter beyond this width.
     */
    protected function calculatePadding(int $start, int $step, int $amount): int
    {
        return \strlen((string) ($start + $step * ($amount - 1)));
    }

    /**
     * Resolve a voucher from either a code or an existing voucher instance.
     *
     * Codes are looked up in the database, whereas instances are used as-is - meaning their
     * current in-memory state is trusted. Refresh the instance first if it might be stale.
     *
     * Returns null when a code has no match, or when an instance doesn't exist in the database.
     *
     * @param \FrittenKeeZ\Vouchers\Models\Voucher|string $voucher Voucher code or instance.
     */
    protected function resolveVoucher(string|Voucher $voucher): ?Voucher
    {
        if (\is_string($voucher)) {
            return $this->vouchers()->withCode($voucher)->first();
        }

        return $voucher->exists ? $voucher : null;
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
