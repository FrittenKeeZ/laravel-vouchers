<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Models;

use FrittenKeeZ\Vouchers\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VoucherEntity extends Model
{
    use Scopes\VoucherEntity;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Constructor.
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Config::table('entities');

        parent::__construct($attributes);
    }

    /**
     * Associated entity.
     */
    public function entity(): MorphTo
    {
        return $this->morphTo('entity');
    }

    /**
     * Associated voucher.
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Config::model('voucher'));
    }
}
