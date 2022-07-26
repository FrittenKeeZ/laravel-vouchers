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
     *
     * @var \FrittenKeeZ\Vouchers\Models\Redeemer
     */
    public ?Redeemer $redeemer;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'value',
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
    ];

    /**
     * Constructor.
     *
     * @param array $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Config::table('vouchers');

        parent::__construct($attributes);
    }

    /**
     * Whether voucher has prefix, optionally specifying a separator different from config.
     *
     * @param string      $prefix
     * @param string|null $separator
     *
     * @return bool
     */
    public function hasPrefix(string $prefix, ?string $separator = null): bool
    {
        $clause = sprintf('%s%s', $prefix, $separator === null ? config('vouchers.separator') : $separator);

        return Str::startsWith($this->code, $clause);
    }

    /**
     * Whether voucher has suffix, optionally specifying a separator different from config.
     *
     * @param string      $suffix
     * @param string|null $separator
     *
     * @return bool
     */
    public function hasSuffix(string $suffix, ?string $separator = null): bool
    {
        $clause = sprintf('%s%s', $separator === null ? config('vouchers.separator') : $separator, $suffix);

        return Str::endsWith($this->code, $clause);
    }

    /**
     * Whether voucher is started.
     *
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->lte(Carbon::now());
    }

    /**
     * Whether voucher is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->lte(Carbon::now());
    }

    /**
     * Whether voucher is redeemed.
     *
     * @return bool
     */
    public function isRedeemed(): bool
    {
        return $this->redeemed_at !== null;
    }

    /**
     * Whether voucher is redeemable.
     *
     * @return bool
     */
    public function isRedeemable(): bool
    {
        return !$this->isRedeemed() && $this->isStarted() && !$this->isExpired();
    }

    /**
     * Redeem voucher with the provided redeemer.
     *
     * @param \FrittenKeeZ\Vouchers\Models\Redeemer $redeemer
     *
     * @return bool
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
     * Associated owner entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function owner(): MorphTo
    {
        return $this->morphTo('owner');
    }

    /**
     * Associated voucher entities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function voucherEntities(): HasMany
    {
        return $this->hasMany(Config::model('entity'));
    }

    /**
     * Associated redeemers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function redeemers(): HasMany
    {
        return $this->hasMany(Config::model('redeemer'));
    }

    /**
     * Add related entities.
     *
     * @param \Illuminate\Database\Eloquent\Model ...$entities
     *
     * @return void
     */
    public function addEntities(Model ...$entities): void
    {
        if (!empty($entities)) {
            $model = Config::model('entity');
            $models = collect($entities)->map(function (Model $entity) use ($model) {
                return $model::make()->entity()->associate($entity);
            });
            $this->voucherEntities()->saveMany($models);
        }
    }

    /**
     * Get all associated entities - optionally with a specific type (class or alias).
     *
     * @param string|null $type
     *
     * @return \Illuminate\Support\Collection
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
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function redeeming($callback): void
    {
        static::registerModelEvent('redeeming', $callback);
    }

    /**
     * Register a redeemed voucher event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function redeemed($callback): void
    {
        static::registerModelEvent('redeemed', $callback);
    }

    /**
     * Register a shouldMarkRedeemed voucher event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function shouldMarkRedeemed($callback): void
    {
        static::registerModelEvent('shouldMarkRedeemed', $callback);
    }
}
