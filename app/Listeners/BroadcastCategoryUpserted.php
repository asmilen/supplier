<?php

namespace App\Listeners;

use App\Events\CategoryUpserted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class BroadcastCategoryUpserted
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
     * @param  CategoryUpserted  $event
     * @return void
     */
    public function handle(CategoryUpserted $event)
    {
        $event->category->broadcastUpserted();
    }
}
