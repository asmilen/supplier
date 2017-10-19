<?php

namespace App\Listeners;

use App\Events\SupplierUpserted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class BroadcastSupplierUpserted
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SupplierUpserted  $event
     * @return void
     */
    public function handle(SupplierUpserted $event)
    {
        $event->supplier->broadcastUpserted();
    }
}
