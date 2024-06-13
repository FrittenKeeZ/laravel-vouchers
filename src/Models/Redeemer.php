<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Models;

use FrittenKeeZ\Vouchers\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Redeemer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'metadata',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Constructor.
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Config::table('redeemers');

        parent::__construct($attributes);
    }

    /**
     * Associated redeemer entity.
     */
    public function redeemer(): MorphTo
    {
        return $this->morphTo('redeemer');
    }

    /**
     * Associated voucher.
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Config::model('voucher'));
    }
}
