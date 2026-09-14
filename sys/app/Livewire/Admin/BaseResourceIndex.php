<?php

namespace App\Livewire\Admin;

use App\Services\MediaStorageService;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

abstract class BaseResourceIndex extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $status = '';

    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    public bool $showForm = false;

    public bool $showDetail = false;

    public ?int $editingId = null;

    public ?int $detailId = null;

    /** @var array<string, mixed> */
    public array $form = [];

    /** @var array<string, mixed> */
    public array $uploads = [];

    /** @var class-string<Model> */
    protected string $modelClass;

    protected string $permission = '';

    protected string $title = '';

    protected string $description = '';

    protected ?string $builderRouteName = null;

    protected string $builderLabel = 'Constructor';

    /** @var array<string, string> */
    protected array $columns = [];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [];

    /** @var list<string> */
    protected array $searchable = ['name', 'title', 'email'];

    /** @var list<string> */
    protected array $with = [];

    /** @var array<string, array<string, mixed>> */
    protected array $detailOnlyFields = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $this->authorizePermission('view');

        if (! $this->isSortableField($field)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function create(): void
    {
        $this->authorizePermission('create');
        $this->editingId = null;
        $this->form = $this->defaults();
        $this->uploads = $this->defaultUploads();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->authorizePermission('update');

        $record = $this->query(includeTrashed: true)->findOrFail($id);
        $this->editingId = $record->getKey();
        $this->form = collect($this->fields)
            ->keys()
            ->mapWithKeys(fn (string $field): array => [$field => $this->formValue($record->getAttribute($field))])
            ->all();
        $this->uploads = $this->defaultUploads();
        $this->showForm = true;
    }

    public function detail(int $id): void
    {
        $this->authorizePermission('view');
        $this->detailId = $id;
        $this->showDetail = true;
    }

    public function save(): void
    {
        $this->authorizePermission($this->editingId ? 'update' : 'create');

        $this->normalizeUploadProperties();

        $data = $this->validate()['form'];
        $data = $this->mergeUploadedMedia($data);
        $model = $this->editingId
            ? $this->query(includeTrashed: true)->findOrFail($this->editingId)
            : $this->newModel();

        $model->fill($data);
        $model->save();

        $this->showForm = false;
        $this->reset(['editingId', 'form', 'uploads']);
        session()->flash('status', 'Guardado.');
    }

    public function removeGalleryItem(string $field, int $index): void
    {
        $items = $this->form[$field] ?? [];

        if (! is_array($items) || ! array_key_exists($index, $items)) {
            return;
        }

        unset($items[$index]);
        $this->form[$field] = array_values($items);
    }

    public function clearGalleryUploads(string $field): void
    {
        $this->uploads[$field] = [];
    }

    /**
     * @return list<array{url: string, is_video: bool}>
     */
    public function pendingGalleryUploads(string $field): array
    {
        return collect($this->normalizeUploadFiles($this->uploads[$field] ?? null))
            ->map(function (TemporaryUploadedFile $file): array {
                $mimeType = (string) $file->getMimeType();

                return [
                    'url' => $file->temporaryUrl(),
                    'is_video' => str_starts_with($mimeType, 'video/'),
                ];
            })
            ->all();
    }

    public function detailRawValue(Model $record, string $name, array $field): mixed
    {
        $this->authorizePermission('view');
        abort_unless($record instanceof $this->modelClass, 403);
        $field = $this->detailFieldDefinitions()[$name] ?? null;
        abort_if($field === null, 403);

        if (($field['type'] ?? '') === 'tags') {
            return $record->relationLoaded('tags') ? $record->getRelation('tags') : collect();
        }

        if (isset($field['detail_relation'])) {
            return data_get($record, $field['detail_relation']);
        }

        return data_get($record, $name);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function detailFieldDefinitions(): array
    {
        return array_merge($this->fields, $this->detailOnlyFields);
    }

    public function mediaUrl(?string $path): ?string
    {
        return app(MediaStorageService::class)->url($path);
    }

    public function isVideoPath(?string $path): bool
    {
        if ($path === null || $path === '') {
            return false;
        }

        return app(MediaStorageService::class)->isVideoPath($path);
    }

    public function delete(int $id): void
    {
        $this->authorizePermission('delete');

        $record = $this->query(includeTrashed: true)->findOrFail($id);
        $record->delete();
        session()->flash('status', 'Eliminado.');
    }

    public function restore(int $id): void
    {
        $this->authorizePermission('restore');

        $record = $this->query(includeTrashed: true)->findOrFail($id);

        if (method_exists($record, 'restore')) {
            $record->restore();
        }

        session()->flash('status', 'Restaurado.');
    }

    /**
     * @return LengthAwarePaginator<int, Model>
     */
    public function records(): LengthAwarePaginator
    {
        $this->authorizePermission('view');
        $model = $this->newModel();
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return $this->query(includeTrashed: true)
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $nested): void {
                    foreach ($this->searchable as $field) {
                        if (Schema::hasColumn($this->newModel()->getTable(), $field)) {
                            $nested->orWhere($field, 'like', '%'.$this->search.'%');
                        }
                    }
                });
            })
            ->when($this->status !== '' && Schema::hasColumn($model->getTable(), 'status'), fn (Builder $query) => $query->where('status', $this->status))
            ->orderBy($this->isSortableField($this->sortField) ? $this->sortField : $model->getKeyName(), $direction)
            ->paginate(10);
    }

    public function detailRecord(): ?Model
    {
        $this->authorizePermission('view');

        return $this->detailId ? $this->query(includeTrashed: true)->find($this->detailId) : null;
    }

    /**
     * @return array<int|string, string>
     */
    public function optionsFor(string $name): array
    {
        $this->authorizePermission('view');
        $field = $this->fields[$name] ?? [];

        if (! isset($field['options'])) {
            return [];
        }

        $options = $field['options'];

        if (is_array($options) && isset($options['model'])) {
            /** @var class-string<Model> $model */
            $model = $options['model'];
            $label = $options['label'] ?? 'name';

            return $model::query()
                ->orderBy($label)
                ->pluck($label, 'id')
                ->map(fn (mixed $label): string => (string) $label)
                ->all();
        }

        if (is_array($options)) {
            return collect($options)
                ->map(fn (mixed $label): string => (string) $label)
                ->all();
        }

        return [];
    }

    /**
     * @return array{title: string, description: string, columns: array<string, string>, fields: array<string, array<string, mixed>>, canCreate: bool, canBuild: bool, builderRouteName: ?string, builderLabel: string}
     */
    public function meta(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'columns' => $this->columns,
            'fields' => $this->fields,
            'detailFields' => $this->detailFieldDefinitions(),
            'canCreate' => auth()->user()?->can($this->permission.'.create') ?? false,
            'canBuild' => $this->builderRouteName !== null && (auth()->user()?->can($this->permission.'.update') ?? false),
            'builderRouteName' => $this->builderRouteName,
            'builderLabel' => $this->builderLabel,
        ];
    }

    public function render(): View
    {
        return view('components.admin.partials.resource-index');
    }

    public function displayValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if ($value === null || $value === '') {
            return '';
        }

        return match ((string) $value) {
            'active' => 'Activo',
            'inactive' => 'Inactivo',
            'suspended' => 'Suspendido',
            'draft' => 'Borrador',
            'published' => 'Publicado',
            'scheduled' => 'Programado',
            'closed' => 'Cerrado',
            'archived' => 'Archivado',
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmado',
            'cancelled' => 'Cancelado',
            'completed' => 'Completado',
            'unpaid' => 'Sin pagar',
            'partial' => 'Parcial',
            'paid' => 'Pagado',
            'refunded' => 'Reembolsado',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado',
            'read' => 'Leído',
            'answered' => 'Respondido',
            'new' => 'Nuevo',
            'revoked' => 'Revocado',
            'unsubscribed' => 'Desuscrito',
            'presencial' => 'Presencial',
            'virtual' => 'Virtual',
            'hibrido' => 'Híbrido',
            'grabado' => 'Grabado',
            'basico' => 'Básico',
            'intermedio' => 'Intermedio',
            'avanzado' => 'Avanzado',
            'course' => 'Curso',
            'project' => 'Proyecto',
            'general' => 'General',
            default => (string) $value,
        };
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        $rules = collect($this->fields)
            ->mapWithKeys(function (array $field, string $name): array {
                $rules = $field['rules'] ?? ['nullable'];

                if (($field['unique'] ?? false) === true) {
                    $rules[] = Rule::unique($this->newModel()->getTable(), $name)->ignore($this->editingId);
                }

                if (($field['type'] ?? '') === 'select' && is_array($field['options'] ?? null) && ! isset($field['options']['model'])) {
                    $rules[] = Rule::in(array_keys($field['options']));
                }

                return ['form.'.$name => $rules];
            })
            ->all();

        foreach ($this->fields as $name => $field) {
            $type = $field['type'] ?? 'text';

            if (in_array($type, ['image', 'video', 'file'], true)) {
                $rules['uploads.'.$name] = $field['upload_rules'] ?? $this->defaultUploadRules($type);
            }

            if ($type === 'gallery') {
                $rules['uploads.'.$name] = $field['upload_rules'] ?? ['nullable', 'array'];
                $rules['uploads.'.$name.'.*'] = $field['upload_item_rules'] ?? [
                    'file',
                    'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime',
                    'max:102400',
                ];
                $rules['form.'.$name.'.*'] = ['string'];
            }
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return collect($this->fields)
            ->mapWithKeys(fn (array $field, string $name): array => [$name => $field['default'] ?? null])
            ->all();
    }

    /**
     * @return Builder<Model>
     */
    protected function query(bool $includeTrashed = false): Builder
    {
        $query = $this->modelClass::query()->with($this->with);

        if ($includeTrashed && in_array(SoftDeletes::class, class_uses_recursive($this->modelClass), true)) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $query;
    }

    protected function newModel(): Model
    {
        $modelClass = $this->modelClass;

        return new $modelClass;
    }

    protected function formValue(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if (is_array($value)) {
            return $value;
        }

        return $value;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultUploads(): array
    {
        return collect($this->fields)
            ->filter(fn (array $field): bool => in_array($field['type'] ?? 'text', ['image', 'video', 'file', 'gallery'], true))
            ->mapWithKeys(fn (array $field, string $name): array => [
                $name => ($field['type'] ?? 'text') === 'gallery' ? [] : null,
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mergeUploadedMedia(array $data): array
    {
        $storage = app(MediaStorageService::class);

        foreach ($this->fields as $name => $field) {
            $type = $field['type'] ?? 'text';

            if (in_array($type, ['image', 'video', 'file'], true)) {
                $upload = $this->uploads[$name] ?? null;

                if ($upload instanceof TemporaryUploadedFile) {
                    $data[$name] = $storage->store($upload, $this->uploadDirectory($name, $data));
                }
            }

            if ($type === 'gallery') {
                $uploads = $this->normalizeUploadFiles($this->uploads[$name] ?? null);
                $existing = is_array($data[$name] ?? null) ? $data[$name] : [];

                foreach ($uploads as $upload) {
                    $existing[] = $storage->store($upload, $this->uploadDirectory($name, $data));
                }

                $data[$name] = array_values(array_unique($existing));
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function uploadDirectory(string $fieldName, array $data): string
    {
        $field = $this->fields[$fieldName] ?? [];
        $template = $field['directory'] ?? $this->newModel()->getTable().'/'.$fieldName;

        $slug = (string) ($data['slug'] ?? 'general');

        return str_replace(
            ['{slug}', '{id}'],
            [$slug, (string) ($this->editingId ?? 'new')],
            $template
        );
    }

    protected function normalizeUploadProperties(): void
    {
        foreach ($this->fields as $name => $field) {
            if (($field['type'] ?? '') === 'gallery') {
                $this->uploads[$name] = $this->normalizeUploadFiles($this->uploads[$name] ?? null);
            }
        }
    }

    /**
     * @return list<TemporaryUploadedFile>
     */
    protected function normalizeUploadFiles(mixed $uploads): array
    {
        if ($uploads instanceof TemporaryUploadedFile) {
            return [$uploads];
        }

        if (! is_array($uploads)) {
            return [];
        }

        return array_values(array_filter(
            $uploads,
            fn (mixed $file): bool => $file instanceof TemporaryUploadedFile
        ));
    }

    /**
     * @return list<string>
     */
    protected function defaultUploadRules(string $type): array
    {
        return match ($type) {
            'image' => ['nullable', 'image', 'max:4096'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:102400'],
            default => ['nullable', 'file', 'max:20480'],
        };
    }

    protected function authorizePermission(string $action): void
    {
        abort_unless(auth()->user()?->can($this->permission.'.'.$action), 403);
    }

    protected function isSortableField(string $field): bool
    {
        return array_key_exists($field, $this->columns)
            && ! str_contains($field, '.')
            && Schema::hasColumn($this->newModel()->getTable(), $field);
    }
}
