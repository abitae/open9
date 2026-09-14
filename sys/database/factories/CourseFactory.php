<?php

namespace Database\Factories;

use App\Models\CourseCategory;
use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'course_category_id' => CourseCategory::factory(),
            'instructor_id' => Instructor::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'subtitle' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'objectives' => [fake()->sentence(), fake()->sentence()],
            'requirements' => [fake()->sentence()],
            'target_audience' => [fake()->jobTitle()],
            'modality' => 'virtual',
            'level' => 'basico',
            'duration_hours' => fake()->numberBetween(4, 80),
            'price' => fake()->randomFloat(2, 0, 1500),
            'currency' => 'PEN',
            'capacity' => fake()->numberBetween(10, 100),
            'image' => 'courses/cover-demo.jpg',
            'promotional_video_url' => 'https://open9.dev/videos/course-demo',
            'syllabus_file' => 'courses/syllabus-demo.pdf',
            'status' => 'published',
            'is_featured' => false,
            'published_at' => now(),
        ];
    }
}
