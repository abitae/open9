<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseEnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'user_id' => User::factory(),
            'full_name' => fake()->name(),
            'document_type' => 'DNI',
            'document_number' => fake()->numerify('########'),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'city' => fake()->city(),
            'occupation' => fake()->jobTitle(),
            'company' => fake()->company(),
            'enrollment_code' => 'ENR-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'amount' => fake()->randomFloat(2, 100, 1000),
            'registered_at' => now(),
        ];
    }
}
