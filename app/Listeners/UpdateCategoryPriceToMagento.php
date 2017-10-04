<?php

namespace App\Listeners;

use App\Events\CategoryMarginUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateCategoryPriceToMagento
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
     * @param  CategoryMarginUpdated  $event
     * @return void
     */
    public function handle(CategoryMarginUpdated $event)
    {
        $event->category->updatePriceToMagento();
    }
}
