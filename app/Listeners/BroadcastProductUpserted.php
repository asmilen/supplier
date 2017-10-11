<?php

namespace App\Listeners;

use App\Events\ProductUpserted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class BroadcastProductUpserted
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
     * @param  ProductUpserted  $event
     * @return void
     */
    public function handle(ProductUpserted $event)
    {
        $event->product->broadcastUpserted();
    }
}
