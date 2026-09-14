<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Contact;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Instructor;
use App\Models\MediaFile;
use App\Models\NewsletterSubscriber;
use App\Models\Payment;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\UniqueCodeService;
use Database\Seeders\Concerns\SeedsReferenceImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Open9DemoSeeder extends Seeder
{
    use SeedsReferenceImages;

    public function run(): void
    {
        $codes = app(UniqueCodeService::class);

        $users = $this->seedUsers();
        $instructors = $this->seedInstructors($users);
        $courses = $this->seedCourses($instructors);
        $enrollments = $this->seedEnrollments($courses, $users['student'], $codes);

        $this->seedPayments($enrollments, $users['admin'], $codes);
        $this->seedCertificates($enrollments, $codes);
        $this->seedTestimonials();
        $this->seedContacts($users['admin']);
        $this->seedNewsletter();
        $this->seedMedia($users['admin']);
        $this->seedAuditLogs($users['admin'], $courses->first());
    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(): array
    {
        $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();

        $editor = User::query()->updateOrCreate(
            ['email' => 'editor@open9.dev'],
            ['name' => 'Editor Open9', 'password' => Hash::make('password'), 'status' => 'active', 'email_verified_at' => now()]
        );
        $editor->assignRole('editor');

        $instructor = User::query()->updateOrCreate(
            ['email' => 'docente@open9.dev'],
            ['name' => 'Docente Open9', 'password' => Hash::make('password'), 'status' => 'active', 'email_verified_at' => now()]
        );
        $instructor->assignRole('instructor');

        $student = User::query()->updateOrCreate(
            ['email' => 'estudiante@open9.dev'],
            ['name' => 'Estudiante Open9', 'password' => Hash::make('password'), 'status' => 'active', 'email_verified_at' => now()]
        );
        $student->assignRole('student');

        collect([$admin, $editor, $instructor, $student])->each(function (User $user): void {
            UserProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'document_type' => 'DNI',
                    'document_number' => str_pad((string) (70000000 + $user->id), 8, '0', STR_PAD_LEFT),
                    'city' => 'Lima',
                    'country' => 'Peru',
                    'bio' => 'Equipo OPEN9 — automatización, inteligencia artificial y software a medida.',
                    'profession' => 'Tecnología',
                    'company' => 'OPEN9',
                    'social_links' => ['linkedin' => 'https://linkedin.com/company/open9'],
                ]
            );
        });

        return compact('admin', 'editor', 'instructor', 'student');
    }

    /**
     * @param  array<string, User>  $users
     * @return Collection<int, Instructor>
     */
    private function seedInstructors(array $users)
    {
        return collect([
            [
                'user_id' => $users['instructor']->id,
                'name' => 'Docente Open9',
                'email' => 'docente@open9.dev',
                'profession' => 'Laravel Architect',
            ],
            [
                'user_id' => null,
                'name' => 'Mariana Cloud',
                'email' => 'mariana.cloud@open9.dev',
                'profession' => 'Cloud Engineer',
            ],
            [
                'user_id' => null,
                'name' => 'Diego Data',
                'email' => 'diego.data@open9.dev',
                'profession' => 'Data Specialist',
            ],
        ])->map(fn (array $data): Instructor => Instructor::query()->updateOrCreate(
            ['email' => $data['email']],
            $data + [
                'phone' => '+51 999 000 000',
                'bio' => 'Instructor con experiencia en automatización, IA y proyectos tecnológicos.',
                'experience' => 'Más de 8 años liderando equipos y entrenamientos prácticos.',
                'social_links' => ['website' => 'https://open9.dev'],
                'status' => 'active',
            ]
        ));
    }

    /**
     * @param  Collection<int, Instructor>  $instructors
     * @return Collection<int, Course>
     */
    private function seedCourses($instructors)
    {
        $category = CourseCategory::query()->firstOrFail();

        return collect([
            ['title' => 'Automatización de procesos para empresas', 'modality' => 'virtual', 'level' => 'intermedio', 'price' => 690],
            ['title' => 'Agentes de IA y chatbots en WhatsApp', 'modality' => 'virtual', 'level' => 'avanzado', 'price' => 790],
            ['title' => 'Dashboards con datos reales', 'modality' => 'grabado', 'level' => 'basico', 'price' => 390],
            ['title' => 'Software a medida con Laravel', 'modality' => 'hibrido', 'level' => 'intermedio', 'price' => 890],
        ])->map(function (array $data, int $index) use ($category, $instructors): Course {
            $course = Course::query()->updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'course_category_id' => $category->id,
                    'instructor_id' => $instructors[$index % $instructors->count()]->id,
                    'title' => $data['title'],
                    'subtitle' => 'Curso práctico OPEN9',
                    'description' => 'Programa práctico con casos reales de automatización, IA y software para empresas.',
                    'objectives' => ['Automatizar un flujo real', 'Medir tiempo y costos ahorrados', 'Publicar en producción'],
                    'requirements' => ['Uso básico de computadora', 'Un proceso de tu empresa para trabajar'],
                    'target_audience' => ['Dueños de negocio', 'Equipos de operación y tecnología'],
                    'modality' => $data['modality'],
                    'level' => $data['level'],
                    'duration_hours' => 18 + ($index * 6),
                    'start_date' => now()->addWeeks($index + 1),
                    'end_date' => now()->addWeeks($index + 4),
                    'schedule' => 'Martes y jueves 19:00',
                    'price' => $data['price'],
                    'discount_price' => $data['price'] - 100,
                    'currency' => 'PEN',
                    'capacity' => 30,
                    'enrolled_count' => 4 + $index,
                    'image' => $this->referenceImage(['coding-laptop', 'react-code', 'blog-postgres', 'cloud-dashboard'][$index], 800, 450),
                    'promotional_video_url' => 'https://open9.dev/videos/'.Str::slug($data['title']),
                    'syllabus_file' => 'courses/'.Str::slug($data['title']).'/temario.pdf',
                    'certificate_available' => true,
                    'status' => $index === 3 ? 'draft' : 'published',
                    'is_featured' => $index < 2,
                    'seo_title' => $data['title'],
                    'seo_description' => 'Curso Open9.',
                    'seo_keywords' => 'curso, tecnologia, open9',
                    'published_at' => $index === 3 ? null : now()->subDays($index + 1),
                ]
            );

            $this->seedCourseStructure($course);

            return $course;
        });
    }

    private function seedCourseStructure(Course $course): void
    {
        collect(['Fundamentos', 'Construcción', 'Puesta en marcha'])->each(function (string $moduleTitle, int $moduleIndex) use ($course): void {
            CourseModule::query()->updateOrCreate(
                ['course_id' => $course->id, 'title' => $moduleTitle],
                ['description' => 'Módulo práctico del curso.', 'sort_order' => $moduleIndex + 1, 'status' => 'active']
            );
        });

        collect(['Introducción', 'Práctica guiada', 'Reto aplicado', 'Proyecto final'])->each(
            fn (string $lessonTitle, int $lessonIndex): CourseLesson => CourseLesson::query()->updateOrCreate(
                ['course_id' => $course->id, 'title' => $lessonTitle],
                [
                    'description' => 'Lección práctica con ejemplo de empresa.',
                    'image' => 'courses/'.Str::slug($course->title)."/lesson-{$lessonIndex}/cover.jpg",
                    'video_url' => 'https://open9.dev/videos/'.Str::slug($course->title).'-'.$lessonIndex,
                    'content' => 'Contenido de la lección con un caso real en español.',
                    'resources' => [
                        'courses/'.Str::slug($course->title)."/lesson-{$lessonIndex}/slides.pdf",
                        'courses/'.Str::slug($course->title)."/lesson-{$lessonIndex}/files.zip",
                    ],
                    'duration_minutes' => 20 + ($lessonIndex * 10),
                    'sort_order' => $lessonIndex + 1,
                    'is_preview' => $lessonIndex === 0,
                    'status' => 'active',
                ]
            )
        );
    }

    /**
     * @param  Collection<int, Course>  $courses
     * @return Collection<int, CourseEnrollment>
     */
    private function seedEnrollments($courses, User $student, UniqueCodeService $codes)
    {
        return $courses->flatMap(function (Course $course, int $courseIndex) use ($student, $codes) {
            return collect(range(1, 3))->map(function (int $index) use ($course, $courseIndex, $student, $codes): CourseEnrollment {
                $status = ['pending', 'confirmed', 'completed'][$index - 1];
                $paymentStatus = ['unpaid', 'paid', 'paid'][$index - 1];

                return CourseEnrollment::query()->create([
                    'course_id' => $course->id,
                    'user_id' => $index === 1 ? $student->id : null,
                    'full_name' => "Alumno Demo {$courseIndex}{$index}",
                    'document_type' => 'DNI',
                    'document_number' => sprintf('%08d', 41000000 + ($courseIndex * 10) + $index),
                    'email' => "alumno{$courseIndex}{$index}@open9.dev",
                    'phone' => '+51 988 000 000',
                    'city' => 'Lima',
                    'occupation' => 'Developer',
                    'company' => 'Open9 Demo',
                    'enrollment_code' => $codes->make(CourseEnrollment::class, 'enrollment_code', 'ENR'),
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'amount' => $course->discount_price ?? $course->price,
                    'notes' => 'Inscripcion demo.',
                    'registered_at' => now()->subMonths(5 - $courseIndex)->addDays($index),
                    'confirmed_at' => $status === 'pending' ? null : now()->subMonths(5 - $courseIndex)->addDays($index + 1),
                    'created_at' => now()->subMonths(5 - $courseIndex)->addDays($index),
                    'updated_at' => now()->subMonths(5 - $courseIndex)->addDays($index),
                ]);
            });
        });
    }

    /**
     * @param  Collection<int, CourseEnrollment>  $enrollments
     */
    private function seedPayments($enrollments, User $admin, UniqueCodeService $codes): void
    {
        $enrollments->each(function (CourseEnrollment $enrollment, int $index) use ($admin, $codes): void {
            $status = match ($enrollment->payment_status->value) {
                'paid' => 'approved',
                default => $index % 3 === 0 ? 'rejected' : 'pending',
            };

            Payment::query()->create([
                'course_enrollment_id' => $enrollment->id,
                'user_id' => $enrollment->user_id,
                'payment_code' => $codes->make(Payment::class, 'payment_code', 'PAY'),
                'method' => ['yape', 'plin', 'transferencia', 'tarjeta'][$index % 4],
                'amount' => $enrollment->amount,
                'currency' => 'PEN',
                'transaction_number' => 'TRX'.str_pad((string) ($index + 1), 8, '0', STR_PAD_LEFT),
                'voucher_image' => 'vouchers/demo-'.$index.'.jpg',
                'payment_date' => $enrollment->created_at->toDateString(),
                'status' => $status,
                'reviewed_by' => $status === 'pending' ? null : $admin->id,
                'reviewed_at' => $status === 'pending' ? null : $enrollment->created_at->addDay(),
                'notes' => 'Pago demo.',
                'created_at' => $enrollment->created_at,
                'updated_at' => $enrollment->created_at,
            ]);
        });
    }

    /**
     * @param  Collection<int, CourseEnrollment>  $enrollments
     */
    private function seedCertificates($enrollments, UniqueCodeService $codes): void
    {
        $enrollments
            ->filter(fn (CourseEnrollment $enrollment): bool => $enrollment->status->value === 'completed')
            ->each(fn (CourseEnrollment $enrollment): Certificate => Certificate::query()->create([
                'course_enrollment_id' => $enrollment->id,
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
                'certificate_code' => $codes->make(Certificate::class, 'certificate_code', 'CERT'),
                'student_name' => $enrollment->full_name,
                'course_name' => $enrollment->course->title,
                'issued_date' => now()->toDateString(),
                'pdf_path' => 'certificates/demo-'.$enrollment->id.'.pdf',
                'verification_url' => url('/certificados/verificar'),
                'status' => 'active',
            ]));
    }

    private function seedTestimonials(): void
    {
        $testimonials = [
            [
                'name' => 'Ana Rivera',
                'profession' => 'Gerente comercial',
                'company' => 'Inmobiliaria Norte',
                'type' => 'project',
                'content' => 'Antes perdíamos consultas el fin de semana. El agente de WhatsApp responde al instante y nosotros solo tomamos las visitas que sí van a cerrar.',
                'photo' => 'person-1',
            ],
            [
                'name' => 'Luis Torres',
                'profession' => 'Dueño',
                'company' => 'Sabores del Sur',
                'type' => 'project',
                'content' => 'La cocina ve la comanda en pantalla y el cierre de caja sale solo. Recuperamos dos horas cada noche y se acabaron los pedidos extraviados.',
                'photo' => 'person-2',
            ],
            [
                'name' => 'Carla Mendoza',
                'profession' => 'Administradora',
                'company' => 'Clínica San Martín',
                'type' => 'general',
                'content' => 'Los recordatorios por WhatsApp bajaron las inasistencias a la mitad. Recepción dejó de perseguir pacientes y volvió a atender en ventanilla.',
                'photo' => 'person-3',
            ],
            [
                'name' => 'Roberto Sánchez',
                'profession' => 'Socio',
                'company' => 'Estudio Contable Plus',
                'type' => 'project',
                'content' => 'Ya no chaseamos PDFs por correo. Cada cliente sube sus comprobantes y sabemos quién está incompleto antes del vencimiento.',
                'photo' => 'person-4',
            ],
            [
                'name' => 'María Elena Vargas',
                'profession' => 'Gerente',
                'company' => 'Comercio Andes',
                'type' => 'project',
                'content' => 'Unificamos Instagram, WhatsApp y mostrador. Por fin sé qué se vendió ayer y qué está parado, sin armar Excel el domingo.',
                'photo' => 'person-5',
            ],
            [
                'name' => 'Ricardo Mendoza',
                'profession' => 'Director de operaciones',
                'company' => 'Logística Peak',
                'type' => 'project',
                'content' => 'El tablero de rutas y entregas nos ahorró tres horas diarias de coordinación. El equipo ve retrasos antes de que el cliente se queje.',
                'photo' => 'person-6',
            ],
            [
                'name' => 'Sofía Ramírez',
                'profession' => 'COO',
                'company' => 'Fintech Andina',
                'type' => 'general',
                'content' => 'Automatizamos conciliaciones y alertas de fraude. Pasamos de revisar Excel cada mañana a un panel que nos avisa solo cuando hay excepción.',
                'photo' => 'person-7',
            ],
            [
                'name' => 'Carlos Vega',
                'profession' => 'Gerente general',
                'company' => 'Hotel Costa Verde',
                'type' => 'project',
                'content' => 'Reservas, check-in y upselling por WhatsApp funcionan solos. Ocupación subió y recepción dejó de estar saturada en temporada alta.',
                'photo' => 'person-8',
            ],
            [
                'name' => 'Daniela Ortiz',
                'profession' => 'Directora',
                'company' => 'Academia Nova',
                'type' => 'course',
                'content' => 'Matrículas, pagos y recordatorios de clase en un solo flujo. El equipo docente volvió a enfocarse en enseñar, no en perseguir alumnos.',
                'photo' => 'person-9',
            ],
            [
                'name' => 'Andrés Castillo',
                'profession' => 'Fundador',
                'company' => 'Retail Nova',
                'type' => 'general',
                'content' => 'OPEN9 entendió nuestro rubro desde el día uno. En seis semanas teníamos inventario, pedidos y reportes conectados sin cambiar todo el ERP.',
                'photo' => 'person-10',
            ],
            [
                'name' => 'Lucía Fernández',
                'profession' => 'Jefa de ventas',
                'company' => 'Urban Homes',
                'type' => 'project',
                'content' => 'El agente califica presupuesto y distrito. Mis asesores solo visitan a quien sí puede comprar. Cerramos más con el mismo equipo.',
                'photo' => 'person-11',
            ],
            [
                'name' => 'Héctor Rojas',
                'profession' => 'Gerente de marca',
                'company' => 'Grupo Brasa',
                'type' => 'project',
                'content' => 'Reservas y delivery dejaron de pelearse entre Instagram y el teléfono. Cocina ve la demanda real y ya no preparamos de más.',
                'photo' => 'person-12',
            ],
        ];

        foreach ($testimonials as $index => $data) {
            $photo = $data['photo'];
            unset($data['photo']);

            Testimonial::query()->updateOrCreate(
                ['name' => $data['name']],
                $data + [
                    'photo' => $this->referenceImage($photo, 400, 400),
                    'rating' => 5,
                    'status' => 'active',
                    'sort_order' => $index + 1,
                ],
            );
        }
    }

    private function seedContacts(User $admin): void
    {
        $messages = [
            [
                'status' => 'new',
                'name' => 'Patricia Huamán',
                'email' => 'patricia.huaman@inmobiliarianorte.pe',
                'subject' => 'Chatbot para consultas de departamentos',
                'message' => 'Atendemos 40 consultas diarias por WhatsApp y se nos escapan las del fin de semana. Queremos un agente que responda precio, metraje y agende visitas.',
            ],
            [
                'status' => 'read',
                'name' => 'Jorge Palacios',
                'email' => 'jorge@saboresdelsur.pe',
                'subject' => 'Pedidos y cierre de caja',
                'message' => 'Tenemos un restaurante en Surco. Los pedidos llegan por chat y el cierre lo armamos a mano. ¿Pueden automatizar cocina y caja?',
            ],
            [
                'status' => 'answered',
                'name' => 'Dra. Elena Quispe',
                'email' => 'elena.quispe@clinicasanmartin.pe',
                'subject' => 'Recordatorios de citas',
                'message' => 'Nuestra clínica tiene 18% de inasistencias. Necesitamos recordatorios por WhatsApp y reprogramación sin saturar recepción.',
            ],
            [
                'status' => 'archived',
                'name' => 'Marco Díaz',
                'email' => 'marco@contableplus.pe',
                'subject' => 'Recolección de comprobantes',
                'message' => 'Somos un estudio contable con 80 clientes. Perdemos tiempo pidiendo PDFs. Queremos un enlace de carga y un tablero de pendientes.',
            ],
        ];

        foreach ($messages as $index => $data) {
            Contact::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => '+51 977 000 00'.$index,
                    'subject' => $data['subject'],
                    'message' => $data['message'],
                    'source' => 'web',
                    'status' => $data['status'],
                    'assigned_to' => $data['status'] === 'new' ? null : $admin->id,
                    'answered_at' => $data['status'] === 'answered' ? now() : null,
                ],
            );
        }
    }

    private function seedNewsletter(): void
    {
        collect(range(1, 8))->each(
            fn (int $index): NewsletterSubscriber => NewsletterSubscriber::query()->updateOrCreate(
                ['email' => "suscriptor{$index}@open9.dev"],
                ['name' => "Suscriptor {$index}", 'status' => $index === 8 ? 'unsubscribed' : 'active', 'subscribed_at' => now()->subDays($index), 'unsubscribed_at' => $index === 8 ? now() : null]
            )
        );
    }

    private function seedMedia(User $admin): void
    {
        collect(['courses', 'projects', 'blog', 'vouchers'])->each(
            fn (string $folder, int $index): MediaFile => MediaFile::query()->create([
                'user_id' => $admin->id,
                'file_name' => "{$folder}-demo-{$index}.jpg",
                'original_name' => "{$folder}.jpg",
                'mime_type' => 'image/jpeg',
                'extension' => 'jpg',
                'size' => 1024 * ($index + 1),
                'disk' => 'public',
                'path' => "media/{$folder}/demo-{$index}.jpg",
                'url' => "/storage/media/{$folder}/demo-{$index}.jpg",
                'alt_text' => "Imagen demo {$folder}",
                'description' => 'Archivo demo.',
                'folder' => $folder,
            ])
        );
    }

    private function seedAuditLogs(User $admin, ?Course $course): void
    {
        if (! $course) {
            return;
        }

        collect(['created', 'updated', 'published'])->each(
            fn (string $action): AuditLog => AuditLog::query()->create([
                'user_id' => $admin->id,
                'action' => $action,
                'module' => 'courses',
                'model_type' => Course::class,
                'model_id' => $course->id,
                'old_values' => $action === 'created' ? null : ['status' => 'draft'],
                'new_values' => ['status' => $course->status->value],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Open9DemoSeeder',
            ])
        );
    }
}
