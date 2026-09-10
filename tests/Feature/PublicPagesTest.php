<?php

use App\Livewire\Admin\Enrollments;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

it('renders the enrollment form with the course title', function (): void {
    $course = Course::factory()->create([
        'title' => 'Cloud para empresas',
        'slug' => 'cloud-para-empresas',
    ]);

    $this->get(route('courses.enrollment', $course->slug))
        ->assertOk()
        ->assertSee('Cloud para empresas')
        ->assertSee('Enviar inscripción')
        ->assertSee('OPEN9')
        ->assertSee('logo_normal.png')
        ->assertSee('favicon.png');
});

it('stores a course enrollment from the public form', function (): void {
    $course = Course::factory()->create();

    $this->post(route('courses.enrollment.store', $course->slug), [
        'full_name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'phone' => '999111222',
        'city' => 'Lima',
    ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $enrollment = CourseEnrollment::query()->where('email', 'ada@example.com')->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->course_id)->toBe($course->id)
        ->and($enrollment->full_name)->toBe('Ada Lovelace');
});

it('shows public enrollments in the admin enrollments module', function (): void {
    $this->seed(DatabaseSeeder::class);

    $course = Course::query()->where('status', 'published')->firstOrFail();
    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();

    $this->post(route('courses.enrollment.store', $course->slug), [
        'full_name' => 'Inscripción Pública QA',
        'email' => 'inscripcion.qa@example.com',
        'phone' => '999000111',
        'city' => 'Lima',
    ])->assertRedirect();

    expect(CourseEnrollment::query()->where('email', 'inscripcion.qa@example.com')->exists())->toBeTrue();

    Livewire::actingAs($admin)
        ->test(Enrollments::class)
        ->assertSee('Inscripción Pública QA')
        ->assertSee('inscripcion.qa@example.com');
});

it('renders the certificate verification placeholder', function (): void {
    $this->get(route('certificates.verify', 'CERT-DEMO'))
        ->assertOk()
        ->assertSee('Verificar certificado')
        ->assertSee('OPEN9')
        ->assertSee('logo_normal.png')
        ->assertSee('favicon.png');
});
