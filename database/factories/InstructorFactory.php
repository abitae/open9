<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstructorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'profession' => fake()->jobTitle(),
            'bio' => fake()->paragraph(),
            'experience' => fake()->paragraph(),
            'social_links' => [],
            'status' => 'active',
        ];
    }
}
