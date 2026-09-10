<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class ExpirePendingOrders extends Command
{
    protected $signature = 'orders:expire-pending {--minutes=60 : Minutos de antigüedad para expirar}';

    protected $description = 'Cancela pedidos pendientes sin pago que superan el tiempo límite.';

    public function handle(): int
    {
        $minutes = max(1, (int) $this->option('minutes'));
        $threshold = now()->subMinutes($minutes);

        $expired = Order::query()
            ->where('status', 'pending')
            ->where('payment_status', 'unpaid')
            ->where('created_at', '<', $threshold)
            ->update([
                'status' => 'cancelled',
                'payment_status' => 'failed',
            ]);

        $this->info("Pedidos expirados: {$expired}");

        return self::SUCCESS;
    }
}
