<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseLessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'image' => 'courses/lesson-demo.jpg',
            'video_url' => 'https://open9.dev/videos/demo',
            'content' => fake()->paragraphs(2, true),
            'resources' => ['slides.pdf', 'source-code.zip'],
            'duration_minutes' => fake()->numberBetween(10, 90),
            'sort_order' => fake()->numberBetween(1, 10),
            'is_preview' => false,
            'status' => 'active',
        ];
    }
}
