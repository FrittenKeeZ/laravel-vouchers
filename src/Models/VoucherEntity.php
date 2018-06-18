<?php

namespace FrittenKeeZ\Vouchers\Models;

use FrittenKeeZ\Vouchers\Config;
use Illuminate\Database\Eloquent\Model;

class VoucherEntity extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Constructor.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Config::table('entities');

        parent::__construct($attributes);
    }

    /**
     * Associated entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function entity()
    {
        return $this->morphTo('entity');
    }

    /**
     * Associated voucher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voucher()
    {
        return $this->belongsTo(Config::model('voucher'));
    }
}
