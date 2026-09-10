<section class="space-y-8">
    @php($series = $this->monthlySeries())

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ $this->greeting() }}</flux:heading>
            <flux:text class="mt-1 text-sm text-zinc-500">
                {{ $this->todayLabel() }} · Resumen operativo del frontend OPEN9: tienda, clientes, contenido CMS, contacto y chat IA.
            </flux:text>
        </div>
        <flux:button tag="a" href="{{ config('app.frontend_url', '/') }}" target="_blank" variant="primary" size="sm" icon="arrow-top-right-on-square">
            Ver sitio público
        </flux:button>
    </div>

    @if ($this->attentionItems() !== [])
        <div>
            <flux:heading size="sm" class="mb-3">Pendientes de atención</flux:heading>
            <div class="grid gap-2 md:grid-cols-2">
                @foreach ($this->attentionItems() as $item)
                    <a href="{{ $item['href'] }}" wire:navigate class="flex items-start gap-3 rounded-2xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 transition hover:border-amber-400/50">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-400/15 text-amber-500 dark:text-amber-300">
                            <flux:icon :icon="$item['icon']" variant="mini" class="size-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-50">{{ $item['title'] }}</div>
                            <div class="text-xs text-zinc-500">{{ $item['hint'] }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($this->highlightMetrics() as $metric)
            <x-admin.metric-card
                :label="$metric['label']"
                :value="$metric['value']"
                :hint="$metric['hint'] ?? null"
                :href="$metric['href'] ?? null"
                :icon="$metric['icon'] ?? null"
                :tone="$metric['tone'] ?? 'default'"
            />
        @endforeach
    </div>

    <div class="grid gap-3 xl:grid-cols-3">
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm xl:col-span-2 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-2">
                <flux:heading size="sm">Actividad de 6 meses</flux:heading>
                <span class="text-xs text-zinc-500">Contactos, clientes y pedidos</span>
            </div>
            <div class="mt-3 h-64">
                <canvas id="activity-chart" aria-label="Gráfico de actividad mensual"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-3">Accesos rápidos</flux:heading>
            <div class="grid gap-2">
                @forelse ($this->quickLinks() as $link)
                    <flux:button
                        tag="a"
                        :href="route($link['route'])"
                        wire:navigate
                        variant="subtle"
                        class="justify-start"
                        :icon="$link['icon']"
                    >
                        {{ $link['label'] }}
                    </flux:button>
                @empty
                    <div class="py-6 text-center text-sm text-zinc-500">Sin accesos disponibles.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid gap-3 lg:grid-cols-3">
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-2">
                <flux:heading size="sm">Últimos contactos</flux:heading>
                @can('contacts.view')
                    <flux:button tag="a" :href="route('admin.contacts.index')" wire:navigate variant="ghost" size="xs">Ver todos</flux:button>
                @endcan
            </div>
            <div class="mt-2 divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                @forelse ($this->latestContactRows() as $row)
                    <div class="flex items-start justify-between gap-3 py-2.5">
                        <div class="min-w-0">
                            <div class="truncate font-medium">{{ $row['title'] }}</div>
                            <div class="truncate text-xs text-zinc-500">{{ $row['subtitle'] }}</div>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <flux:badge size="sm" :color="$row['badge_color']">{{ $row['badge'] }}</flux:badge>
                            <span class="text-[11px] text-zinc-500">{{ $row['when'] }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-sm text-zinc-500">Sin mensajes.</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-2">
                <flux:heading size="sm">Últimos pedidos</flux:heading>
                @can('orders.view')
                    <flux:button tag="a" :href="route('admin.orders.index')" wire:navigate variant="ghost" size="xs">Ver todos</flux:button>
                @endcan
            </div>
            <div class="mt-2 divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                @forelse ($this->latestOrderRows() as $row)
                    <div class="flex items-start justify-between gap-3 py-2.5">
                        <div class="min-w-0">
                            <div class="truncate font-medium">{{ $row['title'] }}</div>
                            <div class="truncate text-xs text-zinc-500">{{ $row['subtitle'] }}</div>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <flux:badge size="sm" :color="$row['badge_color']">{{ $row['badge'] }}</flux:badge>
                            <span class="text-[11px] text-zinc-500">{{ $row['when'] }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-sm text-zinc-500">Sin pedidos.</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-2">
                <flux:heading size="sm">Últimos clientes</flux:heading>
                @can('clients.view')
                    <flux:button tag="a" :href="route('admin.clients.index')" wire:navigate variant="ghost" size="xs">Ver todos</flux:button>
                @endcan
            </div>
            <div class="mt-2 divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                @forelse ($this->latestClientRows() as $row)
                    <div class="flex items-start justify-between gap-3 py-2.5">
                        <div class="min-w-0">
                            <div class="truncate font-medium">{{ $row['title'] }}</div>
                            <div class="truncate text-xs text-zinc-500">{{ $row['subtitle'] }}</div>
                        </div>
                        <span class="shrink-0 text-[11px] text-zinc-500">{{ $row['when'] }}</span>
                    </div>
                @empty
                    <div class="py-6 text-center text-sm text-zinc-500">Sin clientes registrados.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div>
        <flux:heading size="sm" class="mb-3">Tienda y clientes</flux:heading>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ($this->storeMetrics() as $metric)
                <x-admin.metric-card
                    :label="$metric['label']"
                    :value="$metric['value']"
                    :hint="$metric['hint'] ?? null"
                    :href="$metric['href'] ?? null"
                    :icon="$metric['icon'] ?? null"
                />
            @endforeach
        </div>
    </div>

    <div>
        <flux:heading size="sm" class="mb-3">Contenido del sitio</flux:heading>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($this->contentMetrics() as $metric)
                <x-admin.metric-card
                    :label="$metric['label']"
                    :value="$metric['value']"
                    :hint="$metric['hint'] ?? null"
                    :href="$metric['href'] ?? null"
                    :icon="$metric['icon'] ?? null"
                />
            @endforeach
        </div>
    </div>

    <div>
        <flux:heading size="sm" class="mb-3">Academia</flux:heading>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($this->academyMetrics() as $metric)
                <x-admin.metric-card
                    :label="$metric['label']"
                    :value="$metric['value']"
                    :href="$metric['href'] ?? null"
                    :icon="$metric['icon'] ?? null"
                />
            @endforeach
        </div>
    </div>

    @script
        <script>
            window.renderOpen9DashboardCharts(@json($series));
        </script>
    @endscript
</section>
