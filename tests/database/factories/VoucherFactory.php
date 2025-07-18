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

    /**
     * Define the redeemed state.
     */
    public function redeemed(): static
    {
        return $this->state([
            'redeemed_at' => $this->faker->dateTime(),
        ]);
    }

    /**
     * Define the unstarted state.
     */
    public function started(bool $started = true): static
    {
        return $this->state([
            'starts_at' => $started
                ? $this->faker->dateTime('-1 day')
                : $this->faker->dateTimeBetween('+1 day', '+1 month'),
        ]);
    }

    /**
     * Define the expired state.
     */
    public function expired(bool $expired = true): static
    {
        return $this->state([
            'expires_at' => $expired
                ? $this->faker->dateTime('-1 day')
                : $this->faker->dateTimeBetween('+1 dat', '+1 month'),
        ]);
    }
}
