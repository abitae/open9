<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFileFactory extends Factory
{
    public function definition(): array
    {
        $fileName = fake()->uuid().'.jpg';

        return [
            'user_id' => User::factory(),
            'file_name' => $fileName,
            'original_name' => 'image.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => fake()->numberBetween(1000, 500000),
            'disk' => 'public',
            'path' => 'media/'.$fileName,
            'url' => '/storage/media/'.$fileName,
            'alt_text' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'folder' => 'media',
        ];
    }
}
