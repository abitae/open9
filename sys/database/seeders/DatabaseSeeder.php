<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AdminUserSeeder::class,
            SettingSeeder::class,
            CourseCategorySeeder::class,
            ProjectCategorySeeder::class,
            BlogCategorySeeder::class,
            ServiceCategorySeeder::class,
            ClientSeeder::class,
            Open9DemoSeeder::class,
            SiteCmsSeeder::class,
            ProjectsCatalogSeeder::class,
            ServicesCatalogSeeder::class,
            BlogContentSeeder::class,
            StoreCatalogSeeder::class,
        ]);
    }
}
