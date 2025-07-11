<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests\Database\Factories;

use FrittenKeeZ\Vouchers\Tests\Models\Voucher;
use FrittenKeeZ\Vouchers\Vouchers;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoucherFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Voucher::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => (new Vouchers())->generate(),
        ];
    }
}
