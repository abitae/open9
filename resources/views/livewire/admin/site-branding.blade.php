<section class="space-y-4">
    @php
        $logoPreview = $logoUpload?->temporaryUrl() ?? $this->mediaUrl($form['logo_path'] ?? null);
        $faviconPreview = $faviconUpload?->temporaryUrl() ?? $this->mediaUrl($form['favicon_path'] ?? null);
        $siteLabel = filled($form['site_name'] ?? null) ? trim($form['site_name']) : null;
        $footerBlurb = filled($form['footer_description'] ?? null)
            ? $form['footer_description']
            : 'Servicios tecnológicos integrales: hardware, cloud y software a medida.';
        $copyright = filled($form['copyright_text'] ?? null)
            ? $form['copyright_text']
            : '© '.now()->year.' '.($siteLabel ?? 'OPEN9').'. Todos los derechos reservados.';
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <flux:heading size="xl">Identidad y marca</flux:heading>
            <flux:text class="text-xs max-w-2xl">
                Configura los datos globales que consume el frontend público (React): navbar, favicon, pie de página,
                contacto y SEO. El contenido del hero de inicio se edita en
                <a href="{{ route('admin.home-hero-panel.index') }}" wire:navigate class="underline">Hero — card principal</a>.
            </flux:text>
        </div>
        <flux:button tag="a" href="{{ $this->frontendUrl() }}" target="_blank" variant="ghost" size="sm" icon="arrow-top-right-on-square">
            Ver sitio público
        </flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-4 xl:grid-cols-[1fr_20rem]">
        <form wire:submit="save" class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:separator text="Navbar y favicon (/api/site → branding)" />

            <div class="grid gap-3 md:grid-cols-2">
                <flux:input
                    wire:model="form.site_name"
                    label="Nombre del sitio"
                    placeholder="Opcional: déjalo vacío para mostrar solo el logo"
                    description="Visible junto al logo en navbar y footer."
                />
                <flux:input
                    wire:model="form.tagline"
                    label="Tagline"
                    placeholder="Tecnología que escala contigo"
                    description="Se muestra al final del footer."
                />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <label class="space-y-1 text-sm">
                    <span class="font-medium">Logo principal</span>
                    <flux:text class="text-xs text-zinc-500">
                        Navbar y footer. Fondo claro en el frontend actual — usa PNG/SVG con buen contraste.
                    </flux:text>
                    <input type="file" wire:model="logoUpload" accept="image/png,image/jpeg,image/webp,image/svg+xml,.svg" class="block w-full text-xs">
                    @error('logoUpload')
                        <flux:text class="text-xs text-red-600">{{ $message }}</flux:text>
                    @enderror
                    @if ($logoPreview)
                        <div class="mt-2 rounded-lg border border-brand-gray bg-white p-3 dark:border-zinc-700 dark:bg-brand-black">
                            <img src="{{ $logoPreview }}" alt="Vista previa del logo" class="h-10 max-w-full object-contain">
                        </div>
                    @endif
                </label>

                <label class="space-y-1 text-sm">
                    <span class="font-medium">Favicon</span>
                    <flux:text class="text-xs text-zinc-500">Pestaña del navegador. ICO, PNG, SVG o WebP.</flux:text>
                    <input type="file" wire:model="faviconUpload" accept="image/x-icon,image/vnd.microsoft.icon,image/png,image/svg+xml,image/webp,.ico" class="block w-full text-xs">
                    @error('faviconUpload')
                        <flux:text class="text-xs text-red-600">{{ $message }}</flux:text>
                    @enderror
                    @if ($faviconPreview)
                        <div class="mt-2 flex items-center gap-2">
                            <img src="{{ $faviconPreview }}" alt="Favicon" class="h-8 w-8 object-contain">
                            <flux:text class="text-xs text-zinc-500">Vista previa</flux:text>
                        </div>
                    @endif
                </label>
            </div>

            <flux:separator text="Pie de página" />

            <flux:textarea
                wire:model="form.footer_description"
                label="Descripción del footer"
                rows="3"
                description="Bloque principal bajo el logo en el pie de página."
            />
            <flux:input
                wire:model="form.copyright_text"
                label="Copyright"
                placeholder="© 2026 OPEN9. Todos los derechos reservados."
            />

            <flux:separator text="Contacto público (/contacto y footer)" />

            <div class="grid gap-3 md:grid-cols-2">
                <flux:input wire:model="form.contact_email" label="Email" placeholder="empresario.ia@open9.dev" />
                <flux:input wire:model="form.contact_phone" label="Teléfono" placeholder="+51 999 999 999" />
                <flux:input wire:model="form.contact_address" label="Dirección / ciudad" class="md:col-span-2" />
                <flux:input wire:model="form.website_url" label="Sitio web" placeholder="https://open9.dev" class="md:col-span-2" />
            </div>

            <flux:separator text="SEO" />

            <flux:textarea
                wire:model="form.seo_description"
                label="Meta description"
                rows="2"
                description="Descripción para buscadores y redes. El frontend la aplica al cargar el sitio."
            />

            <flux:separator text="Opcional / reserva" />

            <div class="grid gap-4 md:grid-cols-2">
                <label class="space-y-1 text-sm">
                    <span class="font-medium">Logo alternativo (oscuro)</span>
                    <flux:text class="text-xs text-zinc-500">
                        Reservado para futuros temas oscuros. No se usa en el frontend Softgen actual.
                    </flux:text>
                    <input type="file" wire:model="logoDarkUpload" accept="image/png,image/jpeg,image/webp,image/svg+xml,.svg" class="block w-full text-xs">
                    @error('logoDarkUpload')
                        <flux:text class="text-xs text-red-600">{{ $message }}</flux:text>
                    @enderror
                    @if ($form['logo_dark_path'] ?? null)
                        <div class="mt-2 rounded-lg border border-zinc-200 bg-zinc-900 p-2 dark:border-zinc-700">
                            <img src="{{ $this->mediaUrl($form['logo_dark_path']) }}" alt="Logo oscuro" class="h-10 max-w-full object-contain">
                        </div>
                    @endif
                </label>

                <div class="space-y-2 text-sm">
                    <span class="font-medium">Video de fondo (legacy)</span>
                    <flux:text class="text-xs text-zinc-500">
                        No se usa en el hero Softgen. El inicio usa imagen/video desde «Hero — card principal».
                    </flux:text>
                    <flux:input wire:model="form.background_video_url" label="URL externa (opcional)" placeholder="https://..." />
                    <input type="file" wire:model="backgroundVideoUpload" accept="video/mp4,video/webm,video/quicktime" class="block w-full text-xs">
                    @error('backgroundVideoUpload')
                        <flux:text class="text-xs text-red-600">{{ $message }}</flux:text>
                    @enderror
                </div>
            </div>

            <flux:button type="submit" variant="primary">Guardar identidad</flux:button>
        </form>

        <aside class="space-y-4">
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="sm" class="mb-3">Vista previa del frontend</flux:heading>

                <div class="overflow-hidden rounded-xl border border-brand-gray bg-white text-brand-ink shadow-sm">
                    <div class="flex items-center justify-between gap-2 border-b border-brand-gray bg-white/90 px-3 py-2.5">
                        <div class="flex min-w-0 items-center gap-2">
                            @if ($logoPreview)
                                <img src="{{ $logoPreview }}" alt="" class="h-7 w-auto max-w-32 shrink-0 object-contain">
                            @else
                                <x-brand-logo variant="light" class="h-7 max-w-32" />
                            @endif
                            @if ($siteLabel)
                                <span class="truncate text-sm font-semibold">{{ $siteLabel }}</span>
                            @endif
                        </div>
                        <span class="hidden text-[10px] font-medium uppercase tracking-wider text-[#7d89a0] sm:inline">Navbar</span>
                    </div>

                    <div class="space-y-2 px-3 py-4">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-[#7d89a0]">Contenido de página</p>
                        <div class="h-16 rounded-lg bg-white/70" aria-hidden></div>
                    </div>

                    <div class="border-t border-brand-gray bg-white/95 px-3 py-3">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-[#7d89a0]">Footer</p>
                        <p class="mt-2 line-clamp-3 text-xs leading-relaxed text-[#53617a]">{{ $footerBlurb }}</p>
                        <p class="mt-2 text-[10px] text-[#7d89a0]">{{ $copyright }}</p>
                        @if (filled($form['tagline'] ?? null))
                            <p class="mt-1 text-[10px] text-brand-dark">{{ $form['tagline'] }}</p>
                        @endif
                    </div>
                </div>

                <flux:text class="mt-3 text-xs text-zinc-500">
                    Los enlaces del footer y redes se configuran en módulos aparte.
                </flux:text>
            </div>

            @if (count($this->relatedModules()) > 0)
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="sm" class="mb-2">Módulos relacionados</flux:heading>
                    <ul class="space-y-1.5 text-sm">
                        @foreach ($this->relatedModules() as $module)
                            <li>
                                <a href="{{ route($module['route']) }}" wire:navigate class="text-zinc-600 underline hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                                    {{ $module['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-lg border border-dashed border-zinc-300 p-4 dark:border-zinc-600">
                <flux:heading size="sm" class="mb-2">Mapa API → frontend</flux:heading>
                <dl class="space-y-2 text-xs text-zinc-500">
                    <div><dt class="font-medium text-zinc-700 dark:text-zinc-300">branding.logo_url</dt><dd>Navbar, footer</dd></div>
                    <div><dt class="font-medium text-zinc-700 dark:text-zinc-300">branding.favicon_url</dt><dd>Pestaña del navegador</dd></div>
                    <div><dt class="font-medium text-zinc-700 dark:text-zinc-300">contact.*</dt><dd>/contacto, enlace web en footer</dd></div>
                    <div><dt class="font-medium text-zinc-700 dark:text-zinc-300">branding.seo_description</dt><dd>&lt;meta name="description"&gt;</dd></div>
                </dl>
            </div>
        </aside>
    </div>
</section>
