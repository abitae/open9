<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public const MODULES = [
        'dashboard',
        'users',
        'roles',
        'permissions',
        'projects',
        'project-categories',
        'blog',
        'blog-categories',
        'blog-tags',
        'courses',
        'course-categories',
        'instructors',
        'course-modules',
        'course-lessons',
        'enrollments',
        'payments',
        'certificates',
        'testimonials',
        'contacts',
        'newsletter',
        'media',
        'settings',
        'audit',
        'storage-settings',
        'site-branding',
        'footer-links',
        'social-links',
        'home-stats',
        'home-hero-panel',
        'home-hero-showcase',
        'home-feature-cards',
        'home-workflow-steps',
        'home-quick-links',
        'home-pricing-plans',
        'home-section-headers',
        'legal-pages',
        'ai-chat',
        'services',
        'service-categories',
        'products',
        'product-categories',
        'product-brands',
        'orders',
        'payment-settings',
        'clients',
        'social-login',
    ];

    public const ACTIONS = ['view', 'create', 'update', 'delete', 'restore'];

    public function run(): void
    {
        foreach (self::MODULES as $module) {
            foreach (self::ACTIONS as $action) {
                Permission::findOrCreate("{$module}.{$action}", 'web');
            }
        }

        Permission::findOrCreate('payments.approve', 'web');
        Permission::findOrCreate('payments.reject', 'web');
        Permission::findOrCreate('certificates.issue', 'web');
        Permission::findOrCreate('certificates.revoke', 'web');
    }
}
