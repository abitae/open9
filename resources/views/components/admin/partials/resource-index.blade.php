@php($meta = $this->meta())

<section class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ $meta['title'] }}</flux:heading>
            <flux:text class="mt-0.5 text-sm text-zinc-500">{{ $meta['description'] }}</flux:text>
        </div>

        @if ($meta['canCreate'])
            <flux:button icon="plus" size="sm" variant="primary" wire:click="create">Nuevo</flux:button>
        @endif
    </div>

    @if (session('status'))
        <flux:callout variant="success" size="sm">{{ session('status') }}</flux:callout>
    @endif

    <div class="flex flex-col gap-2 sm:flex-row">
        <flux:input class="min-w-0 flex-1" size="sm" wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar" />
        <flux:select size="sm" wire:model.live="status" class="sm:max-w-44">
            <option value="">Todos</option>
            <option value="active">Activo</option>
            <option value="draft">Borrador</option>
            <option value="published">Publicado</option>
            <option value="pending">Pendiente</option>
            <option value="approved">Aprobado</option>
            <option value="new">Nuevo</option>
        </flux:select>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <table class="w-full text-left text-xs">
            <thead class="bg-zinc-50 text-zinc-600 dark:bg-zinc-950 dark:text-zinc-300">
                <tr>
                    @foreach ($meta['columns'] as $field => $label)
                        <th class="px-3 py-2.5 font-medium">
                            <button type="button" wire:click="sortBy('{{ $field }}')" class="inline-flex items-center gap-1 hover:text-zinc-900 dark:hover:text-white">
                                {{ $label }}
                                @if ($sortField === $field)
                                    <span class="text-accent">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                    @endforeach
                    <th class="w-28 px-3 py-2.5 text-right font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($this->records() as $record)
                    <tr wire:key="record-{{ $record->getKey() }}" class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/70">
                        @foreach ($meta['columns'] as $field => $label)
                            <td class="max-w-72 truncate px-3 py-2.5">
                                @php($value = data_get($record, $field))
                                @if ($value instanceof \BackedEnum)
                                    {{ $this->displayValue($value->value) }}
                                @elseif (is_bool($value))
                                    {{ $value ? 'Sí' : 'No' }}
                                @else
                                    {{ $this->displayValue($value) }}
                                @endif
                            </td>
                        @endforeach
                        <td class="px-3 py-2.5">
                            <div class="flex justify-end gap-1">
                                @if ($meta['canBuild'])
                                    <flux:button icon="rectangle-stack" size="xs" variant="ghost" :href="route($meta['builderRouteName'], $record->getKey())" wire:navigate />
                                @endif
                                <flux:button icon="eye" size="xs" variant="ghost" wire:click="detail({{ $record->getKey() }})" />
                                <flux:button icon="pencil" size="xs" variant="ghost" wire:click="edit({{ $record->getKey() }})" />
                                @if (method_exists($record, 'trashed') && $record->trashed())
                                    <flux:button icon="arrow-path" size="xs" variant="ghost" wire:click="restore({{ $record->getKey() }})" />
                                @else
                                    <flux:button icon="trash" size="xs" variant="danger" wire:confirm="¿Eliminar este registro?" wire:click="delete({{ $record->getKey() }})" />
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($meta['columns']) + 1 }}" class="px-3 py-12 text-center">
                            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Sin registros</p>
                            <p class="mt-1 text-xs text-zinc-500">Prueba otra búsqueda o crea un registro nuevo.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $this->records()->links() }}

    <flux:modal wire:model="showForm" class="md:w-[44rem]">
        <form wire:submit="save" class="flex max-h-[85vh] flex-col">
            <div class="space-y-3 overflow-y-auto pr-1">
                <flux:heading size="lg">{{ $editingId ? 'Editar' : 'Nuevo' }} {{ $meta['title'] }}</flux:heading>

                @foreach ($meta['fields'] as $name => $field)
                    @include('components.admin.partials.resource-form-field', ['fieldName' => $name, 'field' => $field])
                @endforeach

                <div wire:loading wire:target="uploads,save" class="text-xs text-zinc-500">Subiendo archivos...</div>
            </div>

            <div class="mt-4 flex justify-end gap-2 border-t border-zinc-200 pt-3 dark:border-zinc-700">
                <flux:button type="button" variant="ghost" size="sm" wire:click="$set('showForm', false)">Cancelar</flux:button>
                <flux:button type="submit" size="sm">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal wire:model="showDetail" class="md:w-[44rem]">
        @include('components.admin.partials.resource-detail')
    </flux:modal>
</section>
