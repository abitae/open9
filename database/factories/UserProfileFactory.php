<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'document_type' => 'DNI',
            'document_number' => fake()->unique()->numerify('########'),
            'birthdate' => fake()->date(),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'bio' => fake()->paragraph(),
            'profession' => fake()->jobTitle(),
            'company' => fake()->company(),
            'social_links' => [],
        ];
    }
}
