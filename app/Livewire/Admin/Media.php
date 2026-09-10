<?php

namespace App\Livewire\Admin;

use App\Models\MediaFile;

class Media extends BaseResourceIndex
{
    protected string $modelClass = MediaFile::class;

    protected string $permission = 'media';

    protected string $title = 'Archivos';

    protected string $description = 'Catálogo de archivos subidos.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'original_name' => 'Nombre', 'mime_type' => 'MIME', 'folder' => 'Carpeta'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'file_name' => ['label' => 'Nombre de archivo', 'rules' => ['required', 'string', 'max:255']],
        'original_name' => ['label' => 'Nombre original', 'rules' => ['required', 'string', 'max:255']],
        'mime_type' => ['label' => 'MIME', 'rules' => ['required', 'string', 'max:255']],
        'extension' => ['label' => 'Extensión', 'rules' => ['required', 'string', 'max:20']],
        'path' => ['label' => 'Ruta', 'rules' => ['required', 'string', 'max:255']],
        'folder' => ['label' => 'Carpeta', 'rules' => ['nullable', 'string', 'max:255']],
    ];
}
