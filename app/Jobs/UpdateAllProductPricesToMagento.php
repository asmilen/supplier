<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductSupplier;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateAllProductPricesToMagento implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $products = Product::select([
            'products.id', 'product_supplier.region_id'
        ])
            ->join('product_supplier', function ($q) {
                $q->on('product_supplier.product_id', '=', 'products.id')
                    ->where('product_supplier.state', '=', 1)
                    ->where('product_supplier.region_id', '>', 0);
            })
            ->groupBy('products.id', 'product_supplier.region_id')->get();

        $productRegions = [];
        foreach ($products as $product){
            $productRegions[$product->id][] = $product->region_id;
        }

        Product::has('productSuppliers')
            ->active()
            ->chunk(200, function ($products) use ($productRegions) {
                foreach($products as $product)
                if(isset($productRegions[$product->id])){
                        foreach ($productRegions[$product->id] as $region) {
                            dispatch(new UpdateProductPriceToMagento($product, $region));
                        }
                    }
            });
    }
}
