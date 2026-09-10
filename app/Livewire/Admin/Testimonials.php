<?php

namespace App\Livewire\Admin;

use App\Models\Testimonial;

class Testimonials extends BaseResourceIndex
{
    protected string $modelClass = Testimonial::class;

    protected string $permission = 'testimonials';

    protected string $title = 'Testimonios';

    protected string $description = 'Testimonios públicos.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'name' => 'Nombre', 'type' => 'Tipo', 'rating' => 'Puntuación', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'name' => ['label' => 'Nombre', 'rules' => ['required', 'string', 'max:255']],
        'profession' => ['label' => 'Profesión', 'rules' => ['nullable', 'string', 'max:255']],
        'content' => ['label' => 'Contenido', 'type' => 'textarea', 'rules' => ['required', 'string']],
        'rating' => ['label' => 'Puntuación', 'type' => 'number', 'default' => 5, 'rules' => ['integer', 'min:1', 'max:5']],
        'type' => ['label' => 'Tipo', 'type' => 'select', 'default' => 'general', 'options' => ['course' => 'Curso', 'project' => 'Proyecto', 'general' => 'General'], 'rules' => ['required', 'string']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'inactive' => 'Inactivo'], 'rules' => ['required', 'string']],
    ];
}
