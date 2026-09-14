@php
    $type = $field['type'] ?? 'text';
    $name = $fieldName;
@endphp

@if ($type === 'textarea')
    <flux:textarea size="sm" wire:model="form.{{ $name }}" :label="$field['label'] ?? $name" />
@elseif ($type === 'select')
    <flux:select size="sm" wire:model="form.{{ $name }}" :label="$field['label'] ?? $name">
        <option value="">Seleccionar</option>
        @foreach ($this->optionsFor($name) as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </flux:select>
@elseif ($type === 'checkbox')
    <flux:checkbox wire:model="form.{{ $name }}" :label="$field['label'] ?? $name" />
@elseif ($type === 'image')
    <flux:field>
        <flux:label>{{ $field['label'] ?? $name }}</flux:label>
        @if (! empty($form[$name]))
            <img src="{{ $this->mediaUrl($form[$name]) }}" alt="" class="mb-2 h-28 w-full rounded-md object-cover">
        @endif
        <input type="file" wire:model="uploads.{{ $name }}" accept="image/*" class="block w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-zinc-900 file:px-3 file:py-1.5 file:text-xs file:text-white dark:file:bg-zinc-100 dark:file:text-zinc-900">
        <flux:error name="uploads.{{ $name }}" />
    </flux:field>
@elseif ($type === 'video')
    <flux:field>
        <flux:label>{{ $field['label'] ?? $name }}</flux:label>
        @if (! empty($form[$name]))
            <video src="{{ $this->mediaUrl($form[$name]) }}" controls class="mb-2 h-32 w-full rounded-md bg-zinc-100 object-cover dark:bg-zinc-900"></video>
        @endif
        <input type="file" wire:model="uploads.{{ $name }}" accept="video/mp4,video/webm,video/quicktime" class="block w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-zinc-900 file:px-3 file:py-1.5 file:text-xs file:text-white dark:file:bg-zinc-100 dark:file:text-zinc-900">
        <flux:error name="uploads.{{ $name }}" />
    </flux:field>
@elseif ($type === 'gallery')
    <flux:field>
        <flux:label>{{ $field['label'] ?? $name }}</flux:label>
        <flux:text class="mb-2 text-[11px] text-zinc-500">Puedes seleccionar varias imágenes o videos a la vez.</flux:text>

        @if (! empty($form[$name]) && is_array($form[$name]))
            <div class="mb-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach ($form[$name] as $index => $item)
                    <div wire:key="gallery-saved-{{ $name }}-{{ $index }}" class="relative overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700">
                        @if ($this->isVideoPath($item))
                            <video src="{{ $this->mediaUrl($item) }}" controls class="h-24 w-full bg-zinc-100 object-cover dark:bg-zinc-900"></video>
                        @else
                            <img src="{{ $this->mediaUrl($item) }}" alt="" class="h-24 w-full object-cover">
                        @endif
                        <button type="button" wire:click="removeGalleryItem('{{ $name }}', {{ $index }})" class="absolute right-1 top-1 rounded bg-black/60 px-1.5 py-0.5 text-[10px] text-white">Quitar</button>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($this->pendingGalleryUploads($name) !== [])
            <div class="mb-3 rounded-md border border-dashed border-blue-300 bg-blue-50/50 p-2 dark:border-blue-700 dark:bg-blue-950/20">
                <flux:text class="mb-2 text-[11px] font-medium text-blue-700 dark:text-blue-300">Archivos listos para guardar</flux:text>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach ($this->pendingGalleryUploads($name) as $index => $pending)
                        <div wire:key="gallery-pending-{{ $name }}-{{ $index }}" class="overflow-hidden rounded-md border border-blue-200 dark:border-blue-800">
                            @if ($pending['is_video'])
                                <video src="{{ $pending['url'] }}" controls class="h-24 w-full bg-zinc-100 object-cover dark:bg-zinc-900"></video>
                            @else
                                <img src="{{ $pending['url'] }}" alt="" class="h-24 w-full object-cover">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <input type="file" wire:model="uploads.{{ $name }}" multiple accept="image/*,video/mp4,video/webm,video/quicktime" class="block w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-zinc-900 file:px-3 file:py-1.5 file:text-xs file:text-white dark:file:bg-zinc-100 dark:file:text-zinc-900">

        <div class="mt-2 flex items-center justify-between gap-2">
            <flux:text wire:loading wire:target="uploads.{{ $name }}" class="text-[11px] text-zinc-500">Procesando archivos...</flux:text>
            @if ($this->pendingGalleryUploads($name) !== [])
                <button type="button" wire:click="clearGalleryUploads('{{ $name }}')" class="text-[11px] text-zinc-500 underline">Limpiar selección</button>
            @endif
        </div>

        <flux:error name="uploads.{{ $name }}" />
        <flux:error name="uploads.{{ $name }}.*" />
    </flux:field>
@elseif ($type === 'number')
    <flux:input size="sm" type="number" wire:model="form.{{ $name }}" :label="$field['label'] ?? $name" :step="$field['step'] ?? 'any'" />
@else
    <flux:input size="sm" :type="$type" wire:model="form.{{ $name }}" :label="$field['label'] ?? $name" />
@endif
