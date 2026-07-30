<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests\Models;

/**
 * User overriding getMorphClass() without a morph map entry.
 */
class CustomMorphUser extends User
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * {@inheritdoc}
     */
    public function getMorphClass(): string
    {
        return 'custom-morph-user';
    }
}
