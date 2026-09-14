<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Models\CourseEnrollment;

class Enrollments extends BaseResourceIndex
{
    protected string $modelClass = CourseEnrollment::class;

    protected string $permission = 'enrollments';

    protected string $title = 'Inscripciones';

    protected string $description = 'Registros de cursos.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'full_name' => 'Estudiante', 'email' => 'Correo', 'status' => 'Estado', 'payment_status' => 'Pago'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'course_id' => ['label' => 'Curso', 'type' => 'select', 'options' => ['model' => Course::class, 'label' => 'title'], 'rules' => ['required', 'integer']],
        'full_name' => ['label' => 'Nombre completo', 'rules' => ['required', 'string', 'max:255']],
        'email' => ['label' => 'Correo', 'type' => 'email', 'rules' => ['required', 'email', 'max:255']],
        'phone' => ['label' => 'Teléfono', 'rules' => ['nullable', 'string', 'max:255']],
        'enrollment_code' => ['label' => 'Código', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'pending', 'options' => ['pending' => 'Pendiente', 'confirmed' => 'Confirmado', 'cancelled' => 'Cancelado', 'completed' => 'Completado'], 'rules' => ['required', 'string']],
        'payment_status' => ['label' => 'Pago', 'type' => 'select', 'default' => 'unpaid', 'options' => ['unpaid' => 'Sin pagar', 'partial' => 'Parcial', 'paid' => 'Pagado', 'refunded' => 'Reembolsado'], 'rules' => ['required', 'string']],
        'amount' => ['label' => 'Importe', 'type' => 'number', 'default' => 0, 'rules' => ['numeric', 'min:0']],
    ];
}
