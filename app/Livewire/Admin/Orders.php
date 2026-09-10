<?php

namespace App\Livewire\Admin;

use App\Models\Order;

class Orders extends BaseResourceIndex
{
    protected string $modelClass = Order::class;

    protected string $permission = 'orders';

    protected string $title = 'Pedidos';

    protected string $description = 'Órdenes de la tienda.';

    /** @var list<string> */
    protected array $searchable = ['order_code', 'buyer_name', 'buyer_email'];

    /** @var list<string> */
    protected array $with = ['items'];

    /** @var array<string, string> */
    protected array $columns = [
        'id' => 'ID',
        'order_code' => 'Código',
        'buyer_name' => 'Cliente',
        'total' => 'Total',
        'payment_status' => 'Pago',
        'status' => 'Estado',
    ];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'status' => [
            'label' => 'Estado',
            'type' => 'select',
            'options' => [
                'pending' => 'Pendiente',
                'confirmed' => 'Confirmado',
                'cancelled' => 'Cancelado',
                'completed' => 'Completado',
            ],
            'rules' => ['required', 'string'],
        ],
        'payment_status' => [
            'label' => 'Estado de pago',
            'type' => 'select',
            'options' => [
                'unpaid' => 'Sin pagar',
                'pending' => 'Pendiente',
                'paid' => 'Pagado',
                'failed' => 'Fallido',
            ],
            'rules' => ['required', 'string'],
        ],
    ];

    /** @var array<string, array<string, mixed>> */
    protected array $detailOnlyFields = [
        'order_code' => ['label' => 'Código'],
        'buyer_name' => ['label' => 'Cliente'],
        'buyer_email' => ['label' => 'Correo', 'type' => 'email'],
        'buyer_phone' => ['label' => 'Teléfono'],
        'total' => ['label' => 'Total'],
        'currency' => ['label' => 'Moneda'],
        'items' => ['label' => 'Productos', 'type' => 'order_items', 'detail_relation' => 'items'],
        'notes' => ['label' => 'Notas', 'type' => 'textarea'],
        'created_at' => ['label' => 'Fecha', 'type' => 'datetime'],
    ];

    public function create(): void
    {
        abort(403);
    }
}
