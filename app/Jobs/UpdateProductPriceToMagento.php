<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductSupplier;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateProductPriceToMagento implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productSupplier;

    /**
     * Create a new job instance.
     *
     * @param ProductSupplier $productSupplier
     */
    public function __construct(ProductSupplier $productSupplier)
    {
        $this->productSupplier = $productSupplier;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->productSupplier->product->updatePriceToMagento($this->productSupplier->region_id);
    }
}
