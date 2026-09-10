<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['super-admin', 'admin', 'editor', 'instructor', 'student'];

        foreach ($roles as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        Role::findByName('super-admin', 'web')->syncPermissions(Permission::all());

        Role::findByName('admin', 'web')->syncPermissions(
            Permission::query()->whereNotIn('name', ['roles.delete', 'permissions.delete'])->get()
        );

        Role::findByName('editor', 'web')->syncPermissions(
            Permission::query()
                ->where(function ($query) {
                    $query->where('name', 'like', 'blog.%')
                        ->orWhere('name', 'like', 'projects.%')
                        ->orWhere('name', 'like', 'media.%')
                        ->orWhere('name', 'like', 'site-branding.%')
                        ->orWhere('name', 'like', 'footer-links.%')
                        ->orWhere('name', 'like', 'social-links.%')
                        ->orWhere('name', 'like', 'home-%')
                        ->orWhere('name', 'like', 'legal-pages.%')
                        ->orWhere('name', 'like', 'testimonials.%');
                })
                ->get()
        );

        Role::findByName('instructor', 'web')->syncPermissions(
            Permission::query()
                ->where(function ($query) {
                    $query->where('name', 'like', 'courses.view')
                        ->orWhere('name', 'like', 'course-modules.%')
                        ->orWhere('name', 'like', 'course-lessons.%')
                        ->orWhere('name', 'like', 'enrollments.view');
                })
                ->get()
        );
    }
}
