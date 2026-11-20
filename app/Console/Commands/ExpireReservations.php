<?php

namespace App\Console\Commands;

use App\Services\InventoryService;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    protected $signature = 'reservations:expire';
    protected $description = 'Expire timed-out reservations and release inventory.';

    public function handle(InventoryService $inventory): int
    {
        $count = $inventory->expireReservations();
        $this->info("Expired {$count} reservation(s).");

        return self::SUCCESS;
    }
}
