<?php

namespace App\Livewire\Admin;

use App\Models\CourseEnrollment;
use App\Models\Payment;

class Payments extends BaseResourceIndex
{
    protected string $modelClass = Payment::class;

    protected string $permission = 'payments';

    protected string $title = 'Pagos';

    protected string $description = 'Revisión de pagos.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'payment_code' => 'Código', 'method' => 'Método', 'amount' => 'Importe', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'course_enrollment_id' => ['label' => 'Inscripción', 'type' => 'select', 'options' => ['model' => CourseEnrollment::class, 'label' => 'enrollment_code'], 'rules' => ['required', 'integer']],
        'payment_code' => ['label' => 'Código', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'method' => ['label' => 'Método', 'type' => 'select', 'default' => 'transferencia', 'options' => ['yape' => 'yape', 'plin' => 'plin', 'transferencia' => 'transferencia', 'tarjeta' => 'tarjeta', 'efectivo' => 'efectivo', 'otro' => 'otro'], 'rules' => ['required', 'string']],
        'amount' => ['label' => 'Importe', 'type' => 'number', 'rules' => ['required', 'numeric', 'min:0']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'pending', 'options' => ['pending' => 'Pendiente', 'approved' => 'Aprobado', 'rejected' => 'Rechazado', 'refunded' => 'Reembolsado'], 'rules' => ['required', 'string']],
    ];
}
