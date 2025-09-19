<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('STORE-###'),
            'name' => fake()->company(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'open_time' => '09:00',
            'close_time' => '18:00',
            'is_active' => true,
        ];
    }
}
