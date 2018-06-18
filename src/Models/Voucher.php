<?php

namespace FrittenKeeZ\Vouchers\Models;

use FrittenKeeZ\Vouchers\Config;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
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
     * Constructor.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Config::table('vouchers');

        parent::__construct($attributes);
    }

    /**
     * Associated entities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function voucherEntities()
    {
        return $this->hasMany(Config::model('entity'));
    }

    /**
     * Associated redeemers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function redeemers()
    {
        return $this->hasMany(Config::model('redeemer'));
    }

    /**
     * Get all associated entities - optionally with a specific type (class).
     *
     * @param  string|null  $type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEntities(string $type = null)
    {
        if (! empty($type)) {
            return $this->voucherEntities()->where('entity_type', '=', $type)->get()->map->entity;
        }

        return $this->voucherEntities->map->entity;
    }
}
