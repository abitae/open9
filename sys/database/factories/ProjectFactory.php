<?php

namespace Database\Factories;

use App\Models\ProjectCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'project_category_id' => ProjectCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'client_name' => fake()->company(),
            'technology_stack' => ['Laravel', 'Livewire', 'Tailwind CSS'],
            'gallery' => [],
            'status' => 'published',
            'is_featured' => false,
            'published_at' => now(),
        ];
    }
}
