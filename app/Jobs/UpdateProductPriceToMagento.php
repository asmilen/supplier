<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateProductPriceToMagento implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $product;
    protected $regionId;

    /**
     * Create a new job instance.
     *
     * @param Product $product
     * @param $regionId
     */
    public function __construct(Product $product, $regionId)
    {
        $this->product = $product;
        $this->regionId = $regionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->product->updatePriceToMagento($this->regionId);
    }
}
