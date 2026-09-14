<section class="space-y-4">
    <div>
        <flux:heading size="xl">Inventario de la tienda</flux:heading>
        <flux:text class="text-xs">Define si se puede vender cuando el stock llega a cero o queda en negativo.</flux:text>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <form wire:submit="save" class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
        <flux:checkbox wire:model="allow_negative_stock" label="Permitir stock negativo" />
        <flux:text class="text-xs text-zinc-500">
            Si está activo, se puede añadir al carrito y cobrar aunque el stock sea 0 o menor;
            tras cada venta el contador puede bajar de cero. Si está desactivado, el producto
            aparece como agotado al llegar a 0 y el checkout rechaza cantidades superiores al stock.
            Un stock vacío en el producto sigue significando inventario ilimitado.
        </flux:text>

        <flux:button type="submit" variant="primary">Guardar</flux:button>
    </form>
</section>
