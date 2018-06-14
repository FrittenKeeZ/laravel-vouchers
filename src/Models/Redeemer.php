<?php

namespace FrittenKeeZ\Vouchers\Models;

use FrittenKeeZ\Vouchers\Helpers;
use Illuminate\Database\Eloquent\Model;

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
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Helpers::table('redeemers');

        parent::__construct($attributes);
    }

    /**
     * Associated voucher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voucher()
    {
        return $this->belongsTo(Helpers::model('voucher'));
    }
}
