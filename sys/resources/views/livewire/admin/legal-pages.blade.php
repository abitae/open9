<section class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Páginas legales</flux:heading>
            <flux:text class="text-xs">Privacidad, términos, cookies y más.</flux:text>
        </div>
        @can('legal-pages.create')
            <flux:button wire:click="create" icon="plus">Nueva página</flux:button>
        @endcan
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="px-3 py-2 text-left">Título</th>
                    <th class="px-3 py-2 text-left">Slug</th>
                    <th class="px-3 py-2 text-left">Estado</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pages as $page)
                    <tr class="border-b border-zinc-100 dark:border-zinc-800">
                        <td class="px-3 py-2">{{ $page->title }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ $page->slug }}</td>
                        <td class="px-3 py-2">{{ $page->status->value ?? $page->status }}</td>
                        <td class="px-3 py-2 text-right">
                            <flux:button size="sm" wire:click="edit({{ $page->id }})">Editar</flux:button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($showForm)
        <form wire:submit="save" class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <div class="grid gap-3 md:grid-cols-2">
                <flux:input wire:model="form.title" label="Título" />
                <flux:input wire:model="form.slug" label="Slug" />
            </div>
            <flux:select wire:model="form.status" label="Estado">
                <flux:select.option value="draft">Borrador</flux:select.option>
                <flux:select.option value="published">Publicado</flux:select.option>
                <flux:select.option value="archived">Archivado</flux:select.option>
            </flux:select>

            <div class="space-y-3">
                <div class="flex gap-2">
                    <flux:button type="button" size="sm" wire:click="addBlock('heading')">+ Título</flux:button>
                    <flux:button type="button" size="sm" wire:click="addBlock('paragraph')">+ Párrafo</flux:button>
                </div>
                @foreach ($form['blocks'] as $index => $block)
                    <div class="rounded border border-zinc-200 p-3 dark:border-zinc-700">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-xs font-medium uppercase">{{ $block['type'] }}</span>
                            <flux:button type="button" size="sm" variant="ghost" wire:click="removeBlock({{ $index }})">Quitar</flux:button>
                        </div>
                        <flux:textarea wire:model="form.blocks.{{ $index }}.content" rows="{{ $block['type'] === 'heading' ? 2 : 4 }}" />
                    </div>
                @endforeach
            </div>

            <flux:button type="submit" variant="primary">Guardar</flux:button>
        </form>
    @endif
</section>
