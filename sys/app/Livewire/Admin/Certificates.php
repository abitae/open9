<?php

namespace App\Livewire\Admin;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;

class Certificates extends BaseResourceIndex
{
    protected string $modelClass = Certificate::class;

    protected string $permission = 'certificates';

    protected string $title = 'Certificados';

    protected string $description = 'Certificados emitidos.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'certificate_code' => 'Código', 'student_name' => 'Estudiante', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'course_enrollment_id' => ['label' => 'Inscripción', 'type' => 'select', 'options' => ['model' => CourseEnrollment::class, 'label' => 'enrollment_code'], 'rules' => ['required', 'integer']],
        'course_id' => ['label' => 'Curso', 'type' => 'select', 'options' => ['model' => Course::class, 'label' => 'title'], 'rules' => ['required', 'integer']],
        'certificate_code' => ['label' => 'Código', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'student_name' => ['label' => 'Estudiante', 'rules' => ['required', 'string', 'max:255']],
        'course_name' => ['label' => 'Nombre del curso', 'rules' => ['required', 'string', 'max:255']],
        'issued_date' => ['label' => 'Emitido', 'type' => 'date', 'rules' => ['required', 'date']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'revoked' => 'Revocado'], 'rules' => ['required', 'string']],
    ];
}
