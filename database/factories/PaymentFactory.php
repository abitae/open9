<?php

namespace Database\Factories;

use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_enrollment_id' => CourseEnrollment::factory(),
            'user_id' => User::factory(),
            'payment_code' => 'PAY-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
            'method' => 'transferencia',
            'amount' => fake()->randomFloat(2, 100, 1000),
            'currency' => 'PEN',
            'transaction_number' => fake()->optional()->numerify('TRX########'),
            'payment_date' => today(),
            'status' => 'pending',
        ];
    }
}
