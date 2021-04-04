<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];
}
