<section class="space-y-6">
    <div>
        <flux:heading size="xl">Card hero principal</flux:heading>
        <flux:text class="text-xs">Contenido del panel izquierdo del hero: fondo (imagen o video), badge, titular, métricas, CTA, etiquetas y cita.</flux:text>
    </div>

    @if (session('status'))
        <flux:callout variant="success" size="sm">{{ session('status') }}</flux:callout>
    @endif

    <form wire:submit="savePanel" class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
        <flux:heading size="lg">Contenido principal</flux:heading>

        <flux:select size="sm" wire:model.live="panel.media_type" label="Fondo del card">
            <option value="none">Sin imagen ni video (solo vidrio)</option>
            <option value="image">Imagen de fondo</option>
            <option value="video">Video de fondo</option>
        </flux:select>

        @if (($panel['media_type'] ?? 'none') === 'image')
            <label class="space-y-1 text-sm">
                <span class="font-medium">Imagen del card</span>
                <flux:text class="text-xs text-zinc-500">PNG, JPG o WebP. Máximo 8 MB.</flux:text>
                <input type="file" wire:model="imageUpload" accept="image/png,image/jpeg,image/webp" class="block w-full text-xs">
                @error('imageUpload')
                    <flux:text class="text-xs text-red-600">{{ $message }}</flux:text>
                @enderror
                @if ($panel['image_path'] ?? null)
                    <img src="{{ $this->mediaUrl($panel['image_path']) }}" alt="Imagen del card hero" class="mt-2 aspect-video w-1/3 min-w-[9rem] rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                @endif
            </label>
        @endif

        @if (($panel['media_type'] ?? 'none') === 'video')
            <label class="space-y-1 text-sm">
                <span class="font-medium">Video del card</span>
                <flux:text class="text-xs text-zinc-500">MP4, WebM o MOV. Máximo 100 MB.</flux:text>
                <input type="file" wire:model="videoUpload" accept="video/mp4,video/webm,video/quicktime" class="block w-full text-xs">
                @error('videoUpload')
                    <flux:text class="text-xs text-red-600">{{ $message }}</flux:text>
                @enderror
                @if ($panel['video_path'] ?? null)
                    <div class="mt-2 w-1/3 min-w-[9rem]">
                        <video
                            src="{{ $this->mediaUrl($panel['video_path']) }}"
                            class="aspect-video w-full rounded-lg border border-zinc-200 object-cover dark:border-zinc-700"
                            controls
                            muted
                            playsinline
                        ></video>
                    </div>
                @endif
            </label>
        @endif

        <flux:input size="sm" wire:model="panel.badge_label" label="Badge" placeholder="Servicios tecnológicos · open9.dev" />

        <div class="grid gap-3 md:grid-cols-2">
            <flux:input size="sm" wire:model="panel.headline_pre" label="Titular — prefijo" placeholder="Innovando el" />
            <flux:input size="sm" wire:model="panel.headline_highlight" label="Titular — destacado" placeholder="futuro tech" />
        </div>

        <div class="grid gap-3 md:grid-cols-2">
            <flux:input size="sm" wire:model="panel.headline_subtitle" label="Subtítulo — texto" placeholder="Hardware, cloud e" />
            <flux:input size="sm" wire:model="panel.headline_subtitle_highlight" label="Subtítulo — destacado" placeholder="software a medida" />
        </div>

        <flux:checkbox wire:model="panel.show_site_name_chip" label="Mostrar chip con el nombre del sitio en el titular" />

        <flux:textarea size="sm" wire:model="panel.description" label="Descripción" rows="3" />

        <div class="grid gap-3 md:grid-cols-3">
            <flux:input size="sm" wire:model="panel.cta_label" label="CTA — etiqueta" />
            <flux:input size="sm" wire:model="panel.cta_url" label="CTA — URL" placeholder="/contacto" />
            <flux:input size="sm" wire:model="panel.cta_icon" label="CTA — icono Lucide" placeholder="Download" />
        </div>

        <flux:heading size="md">Cita inferior</flux:heading>
        <div class="grid gap-3 md:grid-cols-2">
            <flux:input size="sm" wire:model="panel.quote_kicker" label="Línea superior" />
            <flux:input size="sm" wire:model="panel.quote_footer" label="Pie central" />
            <flux:input size="sm" wire:model="panel.quote_primary" label="Texto principal" />
            <flux:input size="sm" wire:model="panel.quote_secondary" label="Texto secundario (cursiva)" />
        </div>

        <div class="flex justify-end">
            <flux:button type="submit" size="sm" wire:loading.attr="disabled">Guardar contenido</flux:button>
        </div>
    </form>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <div class="flex items-center justify-between gap-2">
                <flux:heading size="lg">Métricas del card</flux:heading>
                <flux:button size="xs" variant="ghost" wire:click="createStat">Nueva</flux:button>
            </div>

            <form wire:submit="saveStat" class="space-y-2 rounded-md border border-dashed border-zinc-300 p-3 dark:border-zinc-600">
                <div class="grid gap-2 sm:grid-cols-2">
                    <flux:input size="sm" wire:model="statForm.value" label="Valor" />
                    <flux:input size="sm" wire:model="statForm.suffix" label="Sufijo" />
                    <flux:input size="sm" wire:model="statForm.label" label="Etiqueta" class="sm:col-span-2" />
                    <flux:input size="sm" type="number" wire:model="statForm.sort_order" label="Orden" />
                    <flux:select size="sm" wire:model="statForm.status" label="Estado">
                        <option value="active">Activo</option>
                        <option value="inactive">Inactivo</option>
                    </flux:select>
                </div>
                <flux:checkbox wire:model="statForm.is_visible" label="Visible" />
                <div class="flex justify-end">
                    <flux:button type="submit" size="sm">{{ $editingStatId ? 'Actualizar' : 'Agregar' }} métrica</flux:button>
                </div>
            </form>

            <div class="overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-3 py-2">Valor</th>
                            <th class="px-3 py-2">Etiqueta</th>
                            <th class="px-3 py-2">Orden</th>
                            <th class="px-3 py-2 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($stats as $stat)
                            <tr wire:key="hero-stat-{{ $stat->id }}">
                                <td class="px-3 py-2">{{ $stat->value }}{{ $stat->suffix }}</td>
                                <td class="px-3 py-2">{{ $stat->label }}</td>
                                <td class="px-3 py-2">{{ $stat->sort_order }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex justify-end gap-1">
                                        <flux:button size="xs" variant="ghost" wire:click="editStat({{ $stat->id }})">Editar</flux:button>
                                        <flux:button size="xs" variant="danger" wire:confirm="¿Eliminar métrica?" wire:click="deleteStat({{ $stat->id }})">Eliminar</flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-zinc-500">Sin métricas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <div class="flex items-center justify-between gap-2">
                <flux:heading size="lg">Etiquetas (pills)</flux:heading>
                <flux:button size="xs" variant="ghost" wire:click="createPill">Nueva</flux:button>
            </div>

            <form wire:submit="savePill" class="space-y-2 rounded-md border border-dashed border-zinc-300 p-3 dark:border-zinc-600">
                <flux:input size="sm" wire:model="pillForm.label" label="Texto" />
                <div class="grid gap-2 sm:grid-cols-2">
                    <flux:input size="sm" type="number" wire:model="pillForm.sort_order" label="Orden" />
                    <flux:select size="sm" wire:model="pillForm.status" label="Estado">
                        <option value="active">Activo</option>
                        <option value="inactive">Inactivo</option>
                    </flux:select>
                </div>
                <flux:checkbox wire:model="pillForm.is_visible" label="Visible" />
                <div class="flex justify-end">
                    <flux:button type="submit" size="sm">{{ $editingPillId ? 'Actualizar' : 'Agregar' }} etiqueta</flux:button>
                </div>
            </form>

            <div class="overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-3 py-2">Texto</th>
                            <th class="px-3 py-2">Orden</th>
                            <th class="px-3 py-2 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($pills as $pill)
                            <tr wire:key="hero-pill-{{ $pill->id }}">
                                <td class="px-3 py-2">{{ $pill->label }}</td>
                                <td class="px-3 py-2">{{ $pill->sort_order }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex justify-end gap-1">
                                        <flux:button size="xs" variant="ghost" wire:click="editPill({{ $pill->id }})">Editar</flux:button>
                                        <flux:button size="xs" variant="danger" wire:confirm="¿Eliminar etiqueta?" wire:click="deletePill({{ $pill->id }})">Eliminar</flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-6 text-center text-zinc-500">Sin etiquetas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
