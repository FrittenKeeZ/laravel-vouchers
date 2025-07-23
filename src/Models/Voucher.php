<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Models;

use FrittenKeeZ\Vouchers\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Voucher extends Model
{
    use Scopes\Voucher;

    /**
     * Active redeemer during events.
     */
    public ?Redeemer $redeemer;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'metadata',
        'starts_at',
        'expires_at',
        'redeemed_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'metadata'    => 'array',
        'starts_at'   => 'datetime',
        'expires_at'  => 'datetime',
        'redeemed_at' => 'datetime',
    ];

    /**
     * User exposed observable events.
     *
     * These are extra user-defined events observers may subscribe to.
     *
     * @var array
     */
    protected $observables = [
        'redeeming',
        'redeemed',
        'shouldMarkRedeemed',
        'unredeeming',
        'unredeemed',
        'shouldMarkUnredeemed',
    ];

    /**
     * Constructor.
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Config::table('vouchers');

        parent::__construct($attributes);
    }

    /**
     * Whether voucher has prefix, optionally specifying a separator different from config.
     */
    public function hasPrefix(string $prefix, ?string $separator = null): bool
    {
        $clause = sprintf('%s%s', $prefix, $separator === null ? config('vouchers.separator') : $separator);

        return Str::startsWith($this->code, $clause);
    }

    /**
     * Whether voucher has suffix, optionally specifying a separator different from config.
     */
    public function hasSuffix(string $suffix, ?string $separator = null): bool
    {
        $clause = sprintf('%s%s', $separator === null ? config('vouchers.separator') : $separator, $suffix);

        return Str::endsWith($this->code, $clause);
    }

    /**
     * Whether voucher is started.
     */
    public function isStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->lte(Carbon::now());
    }

    /**
     * Whether voucher is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->lte(Carbon::now());
    }

    /**
     * Whether voucher is redeemed.
     */
    public function isRedeemed(): bool
    {
        return $this->redeemed_at !== null;
    }

    /**
     * Whether voucher is redeemable.
     */
    public function isRedeemable(): bool
    {
        return !$this->isRedeemed() && $this->isStarted() && !$this->isExpired();
    }

    /**
     * Whether voucher is unredeemable.
     */
    public function isUnredeemable(): bool
    {
        return $this->redeemers()->exists() && $this->isStarted() && !$this->isExpired();
    }

    /**
     * Redeem voucher with the provided redeemer.
     */
    public function redeem(Redeemer $redeemer): bool
    {
        if (!$this->isRedeemable()) {
            return false;
        }

        // Set active redeemer.
        $this->redeemer = $redeemer;

        // If the "redeeming" event returns false we'll bail out of the redeem and return
        // false, indicating that the redeem failed. This provides a chance for any
        // listeners to cancel redeem operations if validations fail or whatever.
        if ($this->fireModelEvent('redeeming') === false) {
            // Unset active redeemer.
            $this->redeemer = null;

            return false;
        }

        // Save related redeemer.
        $this->redeemers()->save($redeemer);
        // Unset redeemers relation to avoid caching issues.
        $this->unsetRelation('redeemers');

        // Update redeemed timestamp unless specified otherwise.
        // This will mark the voucher as redeemed.
        if ($this->fireModelEvent('shouldMarkRedeemed') !== false) {
            $this->redeemed_at = Carbon::now();
        }

        $saved = $this->save();
        // Perform any actions that are necessary after the voucher is saved.
        if ($saved) {
            $this->fireModelEvent('redeemed', false);
        }

        // Unset active redeemer.
        $this->redeemer = null;

        return $saved;
    }

    /**
     * Unredeem voucher with the provided redeemer.
     */
    public function unredeem(Redeemer $redeemer): bool
    {
        if (!$this->isUnredeemable() || !$redeemer->exists || $this->isNot($redeemer->voucher)) {
            return false;
        }

        // Set active redeemer.
        $this->redeemer = $redeemer;

        // If the "unredeeming" event returns false we'll bail out of the unredeem and return
        // false, indicating that the unredeem failed. This provides a chance for any
        // listeners to cancel unredeem operations if validations fail or whatever.
        if ($this->fireModelEvent('unredeeming') === false) {
            // Unset active redeemer.
            $this->redeemer = null;

            return false;
        }

        // Delete related redeemer.
        $redeemer->delete();
        // Unset redeemers relation to avoid caching issues.
        $this->unsetRelation('redeemers');

        // Reset redeemed timestamp unless specified otherwise.
        // This will mark the voucher as unredeemed.
        if ($this->fireModelEvent('shouldMarkUnredeemed') !== false || !$this->redeemers()->exists()) {
            $this->redeemed_at = null;
        }

        $saved = $this->save();
        // Perform any actions that are necessary after the voucher is saved.
        if ($saved) {
            $this->fireModelEvent('unredeemed', false);
        }

        // Unset active redeemer.
        $this->redeemer = null;

        return $saved;
    }

    /**
     * Associated owner entity.
     */
    public function owner(): MorphTo
    {
        return $this->morphTo('owner');
    }

    /**
     * Associated voucher entities.
     */
    public function voucherEntities(): HasMany
    {
        return $this->hasMany(Config::model('entity'));
    }

    /**
     * Associated redeemers.
     */
    public function redeemers(): HasMany
    {
        return $this->hasMany(Config::model('redeemer'));
    }

    /**
     * Add related entities.
     */
    public function addEntities(iterable|Model $entities = [], Model ...$remaining): void
    {
        $entities = collect(is_iterable($entities) ? $entities : [$entities])->concat($remaining);
        if ($entities->isNotEmpty()) {
            $model = Config::model('entity');
            $models = $entities->map(fn (Model $entity) => $model::make()->entity()->associate($entity));
            $this->voucherEntities()->saveMany($models);
        }
    }

    /**
     * Get all associated entities - optionally with a specific type (class or alias).
     */
    public function getEntities(?string $type = null): Collection
    {
        $query = $this->voucherEntities()->with('entity');
        if (!empty($type)) {
            $query->withEntityType($type);
        }

        return $query->get()->map->entity;
    }

    /**
     * Register a redeeming voucher event with the dispatcher.
     *
     * @param array|callable|class-string|\Illuminate\Events\QueuedClosure $callback
     */
    public static function redeeming(mixed $callback): void
    {
        static::registerModelEvent('redeeming', $callback);
    }

    /**
     * Register a redeemed voucher event with the dispatcher.
     *
     * @param array|callable|class-string|\Illuminate\Events\QueuedClosure $callback
     */
    public static function redeemed(mixed $callback): void
    {
        static::registerModelEvent('redeemed', $callback);
    }

    /**
     * Register a shouldMarkRedeemed voucher event with the dispatcher.
     *
     * @param array|callable|class-string|\Illuminate\Events\QueuedClosure $callback
     */
    public static function shouldMarkRedeemed(mixed $callback): void
    {
        static::registerModelEvent('shouldMarkRedeemed', $callback);
    }

    /**
     * Register a unredeeming voucher event with the dispatcher.
     *
     * @param array|callable|class-string|\Illuminate\Events\QueuedClosure $callback
     */
    public static function unredeeming(mixed $callback): void
    {
        static::registerModelEvent('unredeeming', $callback);
    }

    /**
     * Register a unredeemed voucher event with the dispatcher.
     *
     * @param array|callable|class-string|\Illuminate\Events\QueuedClosure $callback
     */
    public static function unredeemed(mixed $callback): void
    {
        static::registerModelEvent('unredeemed', $callback);
    }

    /**
     * Register a shouldMarkUnredeemed voucher event with the dispatcher.
     *
     * @param array|callable|class-string|\Illuminate\Events\QueuedClosure $callback
     */
    public static function shouldMarkUnredeemed(mixed $callback): void
    {
        static::registerModelEvent('shouldMarkUnredeemed', $callback);
    }
}
