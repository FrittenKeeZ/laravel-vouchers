<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests\Database\Factories;

use FrittenKeeZ\Vouchers\Tests\Models\Redeemer;
use Illuminate\Database\Eloquent\Factories\Factory;

class RedeemerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Redeemer::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [];
    }
}
