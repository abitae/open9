<?php

use App\Livewire\Admin\AiChatSettings;
use App\Livewire\Admin\BlogCategories;
use App\Livewire\Admin\BlogPosts;
use App\Livewire\Admin\BlogTags;
use App\Livewire\Admin\Certificates;
use App\Livewire\Admin\Clients;
use App\Livewire\Admin\Contacts;
use App\Livewire\Admin\CourseBuilder;
use App\Livewire\Admin\CourseCategories;
use App\Livewire\Admin\CourseLessons;
use App\Livewire\Admin\CourseModules;
use App\Livewire\Admin\Courses;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Enrollments;
use App\Livewire\Admin\FooterLinkGroups;
use App\Livewire\Admin\FooterLinks;
use App\Livewire\Admin\HomeFeatureCards;
use App\Livewire\Admin\HomeHeroPanelAdmin;
use App\Livewire\Admin\HomeHeroShowcase;
use App\Livewire\Admin\HomePricingPlans;
use App\Livewire\Admin\HomeQuickLinks;
use App\Livewire\Admin\HomeSectionSettings;
use App\Livewire\Admin\HomeStats;
use App\Livewire\Admin\HomeWorkflowSteps;
use App\Livewire\Admin\Instructors;
use App\Livewire\Admin\LegalPages;
use App\Livewire\Admin\Media;
use App\Livewire\Admin\Newsletter;
use App\Livewire\Admin\Orders;
use App\Livewire\Admin\Payments;
use App\Livewire\Admin\PaymentSettings;
use App\Livewire\Admin\Permissions;
use App\Livewire\Admin\ProductBrands;
use App\Livewire\Admin\ProductCategories;
use App\Livewire\Admin\Products;
use App\Livewire\Admin\ProjectCategories;
use App\Livewire\Admin\Projects;
use App\Livewire\Admin\Roles;
use App\Livewire\Admin\ServiceCategories;
use App\Livewire\Admin\Services;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\SiteBrandingAdmin;
use App\Livewire\Admin\SocialLinks;
use App\Livewire\Admin\SocialLoginSettings;
use App\Livewire\Admin\StorageSettings;
use App\Livewire\Admin\Testimonials;
use App\Livewire\Admin\Users;
use App\Models\Contact;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Services\UniqueCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/cursos/{slug}/inscripcion', function (string $slug) {
    return view('public.enrollment', [
        'course' => Course::query()->where('slug', $slug)->firstOrFail(),
    ]);
})->name('courses.enrollment');
Route::post('/cursos/{slug}/inscripcion', function (Request $request, string $slug, UniqueCodeService $codes) {
    $course = Course::query()->where('slug', $slug)->firstOrFail();
    $data = $request->validate([
        'full_name' => ['required', 'string', 'max:255'],
        'document_type' => ['nullable', 'string', 'max:255'],
        'document_number' => ['nullable', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['nullable', 'string', 'max:255'],
        'city' => ['nullable', 'string', 'max:255'],
        'occupation' => ['nullable', 'string', 'max:255'],
        'company' => ['nullable', 'string', 'max:255'],
        'notes' => ['nullable', 'string'],
    ]);

    CourseEnrollment::query()->create($data + [
        'course_id' => $course->id,
        'user_id' => $request->user()?->id,
        'enrollment_code' => $codes->make(CourseEnrollment::class, 'enrollment_code', 'ENR'),
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'amount' => $course->discount_price ?? $course->price,
        'registered_at' => now(),
    ]);

    return back()->with('status', 'Inscripción enviada. Te contactaremos pronto.');
})->middleware('throttle:10,1')->name('courses.enrollment.store');
Route::view('/certificados/verificar/{code}', 'public.placeholder', ['title' => 'Verificar certificado'])->name('certificates.verify');
Route::post('/contacto', function (Request $request) {
    Contact::query()->create($request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['nullable', 'string', 'max:255'],
        'subject' => ['nullable', 'string', 'max:255'],
        'message' => ['required', 'string', 'max:5000'],
    ]) + ['source' => 'web', 'status' => 'new']);

    return back()->with('status', 'Mensaje enviado. Te responderemos pronto.');
})->middleware('throttle:10,1')->name('contact.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', fn () => redirect()->route('admin.dashboard'))->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', Dashboard::class)->middleware('permission:dashboard.view')->name('dashboard');
        Route::get('users', Users::class)->middleware('permission:users.view')->name('users.index');
        Route::get('roles', Roles::class)->middleware('permission:roles.view')->name('roles.index');
        Route::get('permissions', Permissions::class)->middleware('permission:permissions.view')->name('permissions.index');
        Route::get('projects', Projects::class)->middleware('permission:projects.view')->name('projects.index');
        Route::get('project-categories', ProjectCategories::class)->middleware('permission:project-categories.view')->name('project-categories.index');
        Route::get('blog', BlogPosts::class)->middleware('permission:blog.view')->name('blog.index');
        Route::get('blog-categories', BlogCategories::class)->middleware('permission:blog-categories.view')->name('blog-categories.index');
        Route::get('blog-tags', BlogTags::class)->middleware('permission:blog-tags.view')->name('blog-tags.index');
        Route::get('courses', Courses::class)->middleware('permission:courses.view')->name('courses.index');
        Route::get('courses/{course}/builder', CourseBuilder::class)->middleware('permission:courses.update')->name('courses.builder');
        Route::get('course-categories', CourseCategories::class)->middleware('permission:course-categories.view')->name('course-categories.index');
        Route::get('instructors', Instructors::class)->middleware('permission:instructors.view')->name('instructors.index');
        Route::get('course-modules', CourseModules::class)->middleware('permission:course-modules.view')->name('course-modules.index');
        Route::get('course-lessons', CourseLessons::class)->middleware('permission:course-lessons.view')->name('course-lessons.index');
        Route::get('enrollments', Enrollments::class)->middleware('permission:enrollments.view')->name('enrollments.index');
        Route::get('payments', Payments::class)->middleware('permission:payments.view')->name('payments.index');
        Route::get('certificates', Certificates::class)->middleware('permission:certificates.view')->name('certificates.index');
        Route::get('testimonials', Testimonials::class)->middleware('permission:testimonials.view')->name('testimonials.index');
        Route::get('contacts', Contacts::class)->middleware('permission:contacts.view')->name('contacts.index');
        Route::get('newsletter', Newsletter::class)->middleware('permission:newsletter.view')->name('newsletter.index');
        Route::get('media', Media::class)->middleware('permission:media.view')->name('media.index');
        Route::get('settings', Settings::class)->middleware('permission:settings.view')->name('settings.index');
        Route::get('storage-settings', StorageSettings::class)->middleware('permission:storage-settings.view')->name('storage-settings.index');
        Route::get('site-branding', SiteBrandingAdmin::class)->middleware('permission:site-branding.view')->name('site-branding.index');
        Route::get('footer-link-groups', FooterLinkGroups::class)->middleware('permission:footer-links.view')->name('footer-link-groups.index');
        Route::get('footer-links', FooterLinks::class)->middleware('permission:footer-links.view')->name('footer-links.index');
        Route::get('social-links', SocialLinks::class)->middleware('permission:social-links.view')->name('social-links.index');
        Route::get('home-stats', HomeStats::class)->middleware('permission:home-stats.view')->name('home-stats.index');
        Route::get('home-hero-panel', HomeHeroPanelAdmin::class)->middleware('permission:home-hero-panel.view')->name('home-hero-panel.index');
        Route::get('home-hero-showcase', HomeHeroShowcase::class)->middleware('permission:home-hero-showcase.view')->name('home-hero-showcase.index');
        Route::get('home-feature-cards', HomeFeatureCards::class)->middleware('permission:home-feature-cards.view')->name('home-feature-cards.index');
        Route::get('home-workflow-steps', HomeWorkflowSteps::class)->middleware('permission:home-workflow-steps.view')->name('home-workflow-steps.index');
        Route::get('home-quick-links', HomeQuickLinks::class)->middleware('permission:home-quick-links.view')->name('home-quick-links.index');
        Route::get('home-pricing-plans', HomePricingPlans::class)->middleware('permission:home-pricing-plans.view')->name('home-pricing-plans.index');
        Route::get('home-section-headers', HomeSectionSettings::class)->middleware('permission:home-section-headers.view')->name('home-section-headers.index');
        Route::get('legal-pages', LegalPages::class)->middleware('permission:legal-pages.view')->name('legal-pages.index');
        Route::get('ai-chat', AiChatSettings::class)->middleware('permission:ai-chat.view')->name('ai-chat.index');
        Route::get('services', Services::class)->middleware('permission:services.view')->name('services.index');
        Route::get('service-categories', ServiceCategories::class)->middleware('permission:service-categories.view')->name('service-categories.index');
        Route::get('products', Products::class)->middleware('permission:products.view')->name('products.index');
        Route::get('product-categories', ProductCategories::class)->middleware('permission:product-categories.view')->name('product-categories.index');
        Route::get('product-brands', ProductBrands::class)->middleware('permission:product-brands.view')->name('product-brands.index');
        Route::get('orders', Orders::class)->middleware('permission:orders.view')->name('orders.index');
        Route::get('payment-settings', PaymentSettings::class)->middleware('permission:payment-settings.view')->name('payment-settings.index');
        Route::get('clients', Clients::class)->middleware('permission:clients.view')->name('clients.index');
        Route::get('social-login', SocialLoginSettings::class)->middleware('permission:social-login.view')->name('social-login.index');
    });
});

require __DIR__.'/settings.php';

Route::redirect('/login', '/ingresar');
Route::redirect('/register', '/registro');

Route::get('/{any?}', function () {
    $index = public_path('index.html');

    if (file_exists($index)) {
        return response()->file($index);
    }

    return view('welcome');
})->where('any', '^(?!admin|api|storage|up|livewire|flux|vendor|cursos|certificados|settings).*$')->name('home');
