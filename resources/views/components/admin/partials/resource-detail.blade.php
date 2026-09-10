@php
    $record = $this->detailRecord();
    $detailFields = $meta['detailFields'] ?? $meta['fields'];
@endphp

@if ($record)
    <div class="flex max-h-[85vh] flex-col gap-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <flux:heading size="lg">{{ $meta['title'] }}</flux:heading>
                <flux:text class="text-xs text-zinc-500">Registro #{{ $record->getKey() }}</flux:text>
            </div>
            <flux:button type="button" variant="ghost" size="sm" wire:click="$set('showDetail', false)">Cerrar</flux:button>
        </div>

        <div class="space-y-4 overflow-y-auto pr-1">
            @foreach ($detailFields as $name => $field)
                @php
                    $type = $field['type'] ?? 'text';
                    $value = $this->detailRawValue($record, $name, $field);
                    $label = $field['label'] ?? $name;
                @endphp

                <div wire:key="detail-{{ $name }}" class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <flux:text class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-zinc-500">{{ $label }}</flux:text>

                    @if ($type === 'image' && filled($value))
                        <img src="{{ $this->mediaUrl($value) }}" alt="" class="max-h-56 w-full rounded-md object-cover">
                    @elseif ($type === 'video' && filled($value))
                        <video src="{{ $this->mediaUrl($value) }}" controls class="max-h-56 w-full rounded-md bg-zinc-100 object-cover dark:bg-zinc-900"></video>
                    @elseif ($type === 'gallery' && is_array($value) && $value !== [])
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach ($value as $index => $item)
                                <div wire:key="detail-gallery-{{ $name }}-{{ $index }}" class="overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700">
                                    @if ($this->isVideoPath($item))
                                        <video src="{{ $this->mediaUrl($item) }}" controls class="h-28 w-full bg-zinc-100 object-cover dark:bg-zinc-900"></video>
                                    @else
                                        <img src="{{ $this->mediaUrl($item) }}" alt="" class="h-28 w-full object-cover">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @elseif ($type === 'gallery')
                        <flux:text class="text-xs text-zinc-500">Sin archivos en la galería.</flux:text>
                    @elseif ($type === 'textarea' && filled($value))
                        <div class="whitespace-pre-wrap text-sm leading-relaxed text-zinc-700 dark:text-zinc-200">{{ $value }}</div>
                    @elseif ($type === 'tags')
                        <div class="flex flex-wrap gap-1.5">
                            @forelse ($value as $tag)
                                <flux:badge size="sm" color="zinc">{{ $tag->name }}</flux:badge>
                            @empty
                                <flux:text class="text-xs text-zinc-500">Sin etiquetas.</flux:text>
                            @endforelse
                        </div>
                    @elseif ($type === 'order_items')
                        @if (filled($value) && count($value) > 0)
                            <div class="overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400">
                                        <tr>
                                            <th class="px-3 py-2 font-medium">Producto</th>
                                            <th class="px-3 py-2 text-center font-medium">Cant.</th>
                                            <th class="px-3 py-2 text-right font-medium">Precio</th>
                                            <th class="px-3 py-2 text-right font-medium">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach ($value as $item)
                                            <tr wire:key="detail-item-{{ $item->id }}">
                                                <td class="px-3 py-2">{{ $item->product_name }}</td>
                                                <td class="px-3 py-2 text-center">{{ $item->quantity }}</td>
                                                <td class="px-3 py-2 text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                                                <td class="px-3 py-2 text-right">{{ number_format((float) $item->subtotal, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <flux:text class="text-xs text-zinc-500">Sin productos.</flux:text>
                        @endif
                    @elseif ($type === 'checkbox')
                        <flux:badge size="sm" :color="$value ? 'green' : 'zinc'">{{ $value ? 'Sí' : 'No' }}</flux:badge>
                    @elseif ($type === 'url' && filled($value))
                        <a href="{{ $value }}" target="_blank" rel="noopener noreferrer" class="break-all text-sm text-blue-600 hover:underline dark:text-blue-400">{{ $value }}</a>
                    @elseif ($type === 'datetime' && filled($value))
                        <flux:text class="text-sm">{{ $value instanceof \DateTimeInterface ? $value->format('d/m/Y H:i') : $this->displayValue($value) }}</flux:text>
                    @elseif ($type === 'select' && is_array($field['options'] ?? null) && ! isset($field['options']['model']))
                        <flux:text class="text-sm">{{ $this->displayValue($value) }}</flux:text>
                    @elseif (filled($value))
                        <flux:text class="text-sm">{{ $value instanceof \BackedEnum ? $this->displayValue($value->value) : $this->displayValue($value) }}</flux:text>
                    @else
                        <flux:text class="text-xs text-zinc-500">—</flux:text>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
