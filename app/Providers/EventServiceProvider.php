<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\CategoryMarginUpdated' => [
            'App\Listeners\UpdateCategoryPriceToMagento',
        ],
        'App\Events\CategoryUpserted' => [
            'App\Listeners\BroadcastCategoryUpserted',
        ],
        'App\Events\ProductUpserted' => [
            'App\Listeners\BroadcastProductUpserted',
        ],
        'App\Events\SupplierUpserted' => [
            'App\Listeners\BroadcastSupplierUpserted',
            'App\Listeners\OffInactiveSupplierProducts',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
