<?php

namespace Database\Seeders;

use App\Models\CourseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Automatización', 'Inteligencia artificial', 'Programación', 'Cloud', 'Datos'] as $index => $name) {
            CourseCategory::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'status' => 'active', 'sort_order' => $index + 1]
            );
        }
    }
}
