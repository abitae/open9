<?php

use App\Livewire\Admin\BlogCategories;
use App\Livewire\Admin\BlogPosts;
use App\Livewire\Admin\Certificates;
use App\Livewire\Admin\CourseBuilder;
use App\Livewire\Admin\CourseLessons;
use App\Livewire\Admin\CourseModules;
use App\Livewire\Admin\Courses;
use App\Livewire\Admin\Payments;
use App\Livewire\Admin\ProjectCategories;
use App\Livewire\Admin\Projects;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Certificate;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Instructor;
use App\Models\MediaFile;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\UniqueCodeService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

it('seeds the super admin with every permission', function (): void {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();

    expect($admin->hasRole('super-admin'))->toBeTrue()
        ->and($admin->getAllPermissions())->toHaveCount(Permission::query()->count());
});

it('keeps the core academic relationships connected', function (): void {
    $course = Course::factory()->create();
    $module = CourseModule::factory()->for($course)->create();
    $lesson = CourseLesson::factory()->for($course)->create();
    $enrollment = CourseEnrollment::factory()->for($course)->create();
    $payment = Payment::factory()->for($enrollment, 'enrollment')->create();
    $certificate = Certificate::factory()->for($course)->for($enrollment, 'enrollment')->create([
        'student_name' => $enrollment->full_name,
        'course_name' => $course->title,
    ]);

    expect($course->modules)->toHaveCount(1)
        ->and($module->course->is($course))->toBeTrue()
        ->and($course->lessons->first()->is($lesson))->toBeTrue()
        ->and($course->enrollments->first()->is($enrollment))->toBeTrue()
        ->and($enrollment->payments->first()->is($payment))->toBeTrue()
        ->and($enrollment->certificate->is($certificate))->toBeTrue();
});

it('connects blog posts with tags', function (): void {
    $post = BlogPost::factory()->create();
    $tag = BlogTag::factory()->create();

    $post->tags()->attach($tag);

    expect($post->fresh()->tags->first()->is($tag))->toBeTrue();
});

it('generates unique prefixed codes', function (): void {
    $service = app(UniqueCodeService::class);

    $code = $service->make(CourseEnrollment::class, 'enrollment_code', 'ENR');

    expect($code)->toStartWith('ENR-');
});

it('renders the admin dashboard for the seeded super admin', function (): void {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Resumen operativo del frontend OPEN9')
        ->assertSee('Tienda y clientes')
        ->assertSee('Clientes registrados')
        ->assertSee('Pasarela MercadoPago')
        ->assertSee('Login con Google')
        ->assertSee('Ingresos academia')
        ->assertSee('OPEN9')
        ->assertSee('Ver sitio público')
        ->assertSee('Accesos rápidos')
        ->assertSee('Actividad de 6 meses');
});

it('highlights pending work and recent activity on the admin dashboard', function (): void {
    $this->seed(DatabaseSeeder::class);
    $this->travelTo(now()->setTime(10, 15));

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();

    Contact::factory()->create([
        'name' => 'Ada Contacto Dashboard',
        'email' => 'ada.dashboard@example.com',
        'status' => 'new',
    ]);

    Order::query()->create([
        'order_code' => 'ORD-DASH-1',
        'buyer_name' => 'Grace Pedido',
        'buyer_email' => 'grace.dash@example.com',
        'total' => 199.50,
        'currency' => 'PEN',
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);

    Client::factory()->create([
        'name' => 'Cliente Nuevo Dashboard',
        'email' => 'cliente.dash@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Buenos días, Administrador')
        ->assertSee('Pendientes de atención')
        ->assertSee('pedido pendiente de pago')
        ->assertSee('contactos nuevos por revisar')
        ->assertSee('Ada Contacto Dashboard')
        ->assertSee('ORD-DASH-1')
        ->assertSee('Grace Pedido')
        ->assertSee('Cliente Nuevo Dashboard')
        ->assertSee('Nuevo')
        ->assertSee('Pendiente');
});

it('stores public contact messages', function (): void {
    $this->post(route('contact.store'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'subject' => 'Info',
        'message' => 'I want to know more.',
    ])->assertRedirect();

    $this->assertDatabaseHas('contacts', [
        'email' => 'ada@example.com',
        'status' => 'new',
    ]);
});

it('seeds demo data for every admin module', function (): void {
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->count())->toBeGreaterThanOrEqual(4)
        ->and(Instructor::query()->count())->toBeGreaterThanOrEqual(3)
        ->and(Project::query()->count())->toBeGreaterThanOrEqual(4)
        ->and(BlogPost::query()->count())->toBeGreaterThanOrEqual(5)
        ->and(Course::query()->count())->toBeGreaterThanOrEqual(4)
        ->and(CourseModule::query()->count())->toBeGreaterThanOrEqual(12)
        ->and(CourseLesson::query()->count())->toBeGreaterThanOrEqual(16)
        ->and(CourseEnrollment::query()->count())->toBeGreaterThanOrEqual(12)
        ->and(Payment::query()->count())->toBeGreaterThanOrEqual(12)
        ->and(Certificate::query()->count())->toBeGreaterThanOrEqual(4)
        ->and(Testimonial::query()->count())->toBeGreaterThanOrEqual(3)
        ->and(Contact::query()->count())->toBeGreaterThanOrEqual(4)
        ->and(NewsletterSubscriber::query()->count())->toBeGreaterThanOrEqual(8)
        ->and(MediaFile::query()->count())->toBeGreaterThanOrEqual(4);
});

it('renders every admin route with the seeded super admin', function (): void {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();

    collect([
        'admin.dashboard',
        'admin.users.index',
        'admin.roles.index',
        'admin.permissions.index',
        'admin.projects.index',
        'admin.project-categories.index',
        'admin.blog.index',
        'admin.blog-categories.index',
        'admin.blog-tags.index',
        'admin.courses.index',
        'admin.course-categories.index',
        'admin.instructors.index',
        'admin.course-modules.index',
        'admin.course-lessons.index',
        'admin.enrollments.index',
        'admin.payments.index',
        'admin.certificates.index',
        'admin.testimonials.index',
        'admin.contacts.index',
        'admin.newsletter.index',
        'admin.media.index',
        'admin.settings.index',
        'admin.storage-settings.index',
        'admin.site-branding.index',
        'admin.footer-link-groups.index',
        'admin.footer-links.index',
        'admin.social-links.index',
        'admin.home-stats.index',
        'admin.home-hero-panel.index',
        'admin.home-hero-showcase.index',
        'admin.home-feature-cards.index',
        'admin.home-workflow-steps.index',
        'admin.home-quick-links.index',
        'admin.home-pricing-plans.index',
        'admin.home-section-headers.index',
        'admin.legal-pages.index',
        'admin.ai-chat.index',
        'admin.services.index',
        'admin.service-categories.index',
        'admin.products.index',
        'admin.product-categories.index',
        'admin.product-brands.index',
        'admin.orders.index',
        'admin.payment-settings.index',
        'admin.clients.index',
        'admin.social-login.index',
    ])->each(fn (string $routeName) => $this->actingAs($admin)->get(route($routeName))->assertOk());

    $course = Course::query()->firstOrFail();

    $this->actingAs($admin)
        ->get(route('admin.courses.builder', $course))
        ->assertOk()
        ->assertSee('Media del curso')
        ->assertSee('Currícula');
});

it('loads model backed select options in admin forms', function (): void {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();
    $this->actingAs($admin);

    Livewire::test(Courses::class)
        ->call('create')
        ->assertSee(CourseCategory::query()->firstOrFail()->name)
        ->assertSee(Instructor::query()->firstOrFail()->name);

    Livewire::test(CourseModules::class)
        ->call('create')
        ->assertSee(Course::query()->firstOrFail()->title);

    Livewire::test(CourseLessons::class)
        ->call('create')
        ->assertSee(Course::query()->firstOrFail()->title);

    Livewire::test(Payments::class)
        ->call('create')
        ->assertSee(CourseEnrollment::query()->firstOrFail()->enrollment_code);

    Livewire::test(Certificates::class)
        ->call('create')
        ->assertSee(CourseEnrollment::query()->firstOrFail()->enrollment_code)
        ->assertSee(Course::query()->firstOrFail()->title);
});

it('keeps seeded model relationships navigable from both sides', function (): void {
    $this->seed(DatabaseSeeder::class);

    $course = Course::query()->with(['category', 'instructor', 'modules', 'lessons', 'enrollments.payments', 'enrollments.certificate'])->firstOrFail();
    $lesson = CourseLesson::query()->with('course')->firstOrFail();
    $payment = Payment::query()->with(['enrollment.course', 'reviewer'])->whereNotNull('reviewed_by')->firstOrFail();
    $certificate = Certificate::query()->with(['enrollment', 'course', 'user'])->firstOrFail();
    $post = BlogPost::query()->with(['author', 'category', 'tags'])->firstOrFail();
    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();

    expect($course->category)->not->toBeNull()
        ->and($course->instructor)->not->toBeNull()
        ->and($course->image)->not->toBeEmpty()
        ->and($course->promotional_video_url)->not->toBeEmpty()
        ->and($course->syllabus_file)->not->toBeEmpty()
        ->and($course->modules)->not->toBeEmpty()
        ->and($course->lessons)->not->toBeEmpty()
        ->and($course->enrollments->first()->payments)->not->toBeEmpty()
        ->and($lesson->course)->not->toBeNull()
        ->and($lesson->video_url)->not->toBeEmpty()
        ->and($lesson->resources)->not->toBeEmpty()
        ->and($payment->enrollment->course)->not->toBeNull()
        ->and($payment->reviewer)->not->toBeNull()
        ->and($certificate->enrollment)->not->toBeNull()
        ->and($certificate->course)->not->toBeNull()
        ->and($post->author)->not->toBeNull()
        ->and($post->category)->not->toBeNull()
        ->and($post->tags)->not->toBeEmpty()
        ->and($admin->profile)->not->toBeNull()
        ->and($admin->assignedContacts)->not->toBeEmpty()
        ->and($admin->mediaFiles)->not->toBeEmpty()
        ->and($admin->auditLogs)->not->toBeEmpty();
});

it('builds course lessons with uploaded media and resources', function (): void {
    Storage::fake('public');
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();
    $course = Course::query()->firstOrFail();

    Livewire::actingAs($admin)
        ->test(CourseBuilder::class, ['course' => $course])
        ->set('coverImage', UploadedFile::fake()->image('cover.jpg'))
        ->set('promotionalVideo', UploadedFile::fake()->create('intro.mp4', 1000, 'video/mp4'))
        ->set('syllabusFile', UploadedFile::fake()->create('syllabus.pdf', 100, 'application/pdf'))
        ->call('saveCourseMedia')
        ->assertHasNoErrors();

    $course->refresh();

    expect($course->image)->not->toBeEmpty()
        ->and($course->promotional_video_url)->not->toBeEmpty()
        ->and($course->syllabus_file)->not->toBeEmpty();

    Storage::disk('public')->assertExists($course->image);
    Storage::disk('public')->assertExists($course->promotional_video_url);
    Storage::disk('public')->assertExists($course->syllabus_file);

    Livewire::actingAs($admin)
        ->test(CourseBuilder::class, ['course' => $course])
        ->call('newLesson')
        ->set('lessonForm.title', 'Advanced Builder Lesson')
        ->set('lessonForm.description', 'Compact lesson setup.')
        ->set('lessonForm.content', 'Lesson content body.')
        ->set('lessonForm.duration_minutes', 24)
        ->set('lessonForm.sort_order', 99)
        ->set('lessonForm.is_preview', true)
        ->set('lessonForm.status', 'active')
        ->set('lessonForm.resources_text', "https://example.com/guide.pdf\nhttps://example.com/repo.zip")
        ->set('lessonImage', UploadedFile::fake()->image('lesson.jpg'))
        ->set('lessonVideo', UploadedFile::fake()->create('lesson.mp4', 1000, 'video/mp4'))
        ->set('lessonFiles', [UploadedFile::fake()->create('slides.pdf', 100, 'application/pdf')])
        ->call('saveLesson')
        ->assertHasNoErrors();

    $lesson = CourseLesson::query()->where('title', 'Advanced Builder Lesson')->firstOrFail();

    expect($lesson->course->is($course))->toBeTrue()
        ->and($lesson->image)->not->toBeEmpty()
        ->and($lesson->video_url)->not->toBeEmpty()
        ->and($lesson->resources)->toHaveCount(3)
        ->and($lesson->is_preview)->toBeTrue();

    Storage::disk('public')->assertExists($lesson->image);
    Storage::disk('public')->assertExists($lesson->video_url);
});

it('stores uploaded media for content module resources', function (): void {
    Storage::fake('public');
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();
    $projectCategory = ProjectCategory::factory()->create();
    $blogCategory = BlogCategory::factory()->create();
    $suffix = uniqid();

    Livewire::actingAs($admin)
        ->test(ProjectCategories::class)
        ->call('create')
        ->set('form.name', 'Web Apps')
        ->set('form.slug', 'web-apps-'.$suffix)
        ->set('form.status', 'active')
        ->set('form.sort_order', 1)
        ->set('uploads.image', UploadedFile::fake()->image('category.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    $projectCategoryRecord = ProjectCategory::query()->where('slug', 'web-apps-'.$suffix)->firstOrFail();

    expect($projectCategoryRecord->image)->not->toBeEmpty();
    Storage::disk('public')->assertExists($projectCategoryRecord->image);

    Livewire::actingAs($admin)
        ->test(Projects::class)
        ->call('create')
        ->set('form.project_category_id', $projectCategory->getKey())
        ->set('form.title', 'Portal Open9')
        ->set('form.slug', 'portal-open9-'.$suffix)
        ->set('form.status', 'published')
        ->set('form.is_featured', true)
        ->set('uploads.main_image', UploadedFile::fake()->image('project-cover.jpg'))
        ->set('uploads.gallery', [
            UploadedFile::fake()->image('gallery-1.jpg'),
            UploadedFile::fake()->create('demo.mp4', 1000, 'video/mp4'),
        ])
        ->call('save')
        ->assertHasNoErrors();

    $project = Project::query()->where('slug', 'portal-open9-'.$suffix)->firstOrFail();

    expect($project->main_image)->not->toBeEmpty()
        ->and($project->gallery)->toHaveCount(2);

    Storage::disk('public')->assertExists($project->main_image);
    Storage::disk('public')->assertExists($project->gallery[0]);
    Storage::disk('public')->assertExists($project->gallery[1]);

    Livewire::actingAs($admin)
        ->test(BlogCategories::class)
        ->call('create')
        ->set('form.name', 'Tutoriales')
        ->set('form.slug', 'tutoriales-'.$suffix)
        ->set('form.status', 'active')
        ->set('form.sort_order', 2)
        ->set('uploads.image', UploadedFile::fake()->image('blog-category.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    $blogCategoryRecord = BlogCategory::query()->where('slug', 'tutoriales-'.$suffix)->firstOrFail();

    expect($blogCategoryRecord->image)->not->toBeEmpty();
    Storage::disk('public')->assertExists($blogCategoryRecord->image);

    Livewire::actingAs($admin)
        ->test(BlogPosts::class)
        ->call('create')
        ->set('form.user_id', $admin->getKey())
        ->set('form.blog_category_id', $blogCategory->getKey())
        ->set('form.title', 'Guía de medios')
        ->set('form.slug', 'guia-de-medios-'.$suffix)
        ->set('form.status', 'published')
        ->set('uploads.main_image', UploadedFile::fake()->image('post-cover.jpg'))
        ->set('uploads.video_url', UploadedFile::fake()->create('post-video.mp4', 1000, 'video/mp4'))
        ->set('uploads.gallery', [UploadedFile::fake()->image('post-gallery.jpg')])
        ->call('save')
        ->assertHasNoErrors();

    $post = BlogPost::query()->where('slug', 'guia-de-medios-'.$suffix)->firstOrFail();

    expect($post->main_image)->not->toBeEmpty()
        ->and($post->video_url)->not->toBeEmpty()
        ->and($post->gallery)->toHaveCount(1);

    Storage::disk('public')->assertExists($post->main_image);
    Storage::disk('public')->assertExists($post->video_url);
    Storage::disk('public')->assertExists($post->gallery[0]);
});

it('appends gallery files when editing content resources', function (): void {
    Storage::fake('public');
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();
    $suffix = uniqid();

    $project = Project::factory()->create([
        'slug' => 'edit-gallery-'.$suffix,
        'gallery' => ['projects/existing.jpg'],
    ]);

    Livewire::actingAs($admin)
        ->test(Projects::class)
        ->call('edit', $project->getKey())
        ->set('uploads.gallery', [UploadedFile::fake()->image('new-gallery.jpg')])
        ->call('save')
        ->assertHasNoErrors();

    $project->refresh();

    expect($project->gallery)->toHaveCount(2)
        ->and($project->gallery[0])->toBe('projects/existing.jpg');

    Storage::disk('public')->assertExists($project->gallery[1]);
});

it('renders rich detail modals for content resources', function (): void {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();
    $project = Project::query()->with('category')->firstOrFail();
    $post = BlogPost::query()->with(['author', 'category', 'tags'])->firstOrFail();
    $projectCategory = ProjectCategory::query()->firstOrFail();
    $blogCategory = BlogCategory::query()->firstOrFail();

    Livewire::actingAs($admin)
        ->test(Projects::class)
        ->call('detail', $project->getKey())
        ->assertSee('Galería (imágenes y videos)')
        ->assertSee($project->title)
        ->assertSee($project->category?->name ?? '');

    Livewire::actingAs($admin)
        ->test(ProjectCategories::class)
        ->call('detail', $projectCategory->getKey())
        ->assertSee('Imagen')
        ->assertSee($projectCategory->name);

    Livewire::actingAs($admin)
        ->test(BlogPosts::class)
        ->call('detail', $post->getKey())
        ->assertSee('Video principal')
        ->assertSee('Etiquetas')
        ->assertSee($post->author?->name ?? '');

    Livewire::actingAs($admin)
        ->test(BlogCategories::class)
        ->call('detail', $blogCategory->getKey())
        ->assertSee('Imagen')
        ->assertSee($blogCategory->name);
});
