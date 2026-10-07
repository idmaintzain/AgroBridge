<?php

namespace App\Console\Commands;

use App\Services\Escrow;
use Illuminate\Console\Command;

class SettleOrders extends Command
{
    protected $signature = 'orders:settle';

    protected $description = 'Cancel unpaid orders, refund missed collections, and release held payments whose dispute window has closed';

    public function handle(Escrow $escrow): int
    {
        $changed = $escrow->settle();
        $this->info("Settled {$changed} orders.");

        return self::SUCCESS;
    }
}
