<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'group' => fake()->word(),
            'key' => fake()->unique()->slug(2),
            'value' => fake()->sentence(),
            'type' => 'string',
            'is_public' => false,
        ];
    }
}
