<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests\Database\Factories;

use FrittenKeeZ\Vouchers\Tests\Models\Color;
use Illuminate\Database\Eloquent\Factories\Factory;

class ColorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Color::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->colorName,
        ];
    }
}
