<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'profession' => fake()->jobTitle(),
            'company' => fake()->company(),
            'content' => fake()->paragraph(),
            'rating' => fake()->numberBetween(4, 5),
            'type' => 'general',
            'status' => 'active',
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }
}
