<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CertificateFactory extends Factory
{
    public function definition(): array
    {
        $course = Course::factory()->create();

        return [
            'course_enrollment_id' => CourseEnrollment::factory()->for($course),
            'user_id' => User::factory(),
            'course_id' => $course->id,
            'certificate_code' => 'CERT-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
            'student_name' => fake()->name(),
            'course_name' => $course->title,
            'issued_date' => today(),
            'verification_url' => url('/certificados/verificar'),
            'status' => 'active',
        ];
    }
}
