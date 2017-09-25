<?php

namespace App\Jobs;

use App\Http\Controllers\API\SuppliersController;
use App\Models\Product;
use App\Models\ProductSupplier;
use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Sentinel;
use DB;

class UpdateAllProductStatusToMagento implements ShouldQueue
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
        $user = Sentinel::getUser();

        $suppliers = Supplier::leftJoin('product_supplier', 'product_supplier.supplier_id', 'suppliers.id')
            ->where('product_supplier.region_id', '>', 0)
            ->where('product_supplier.status', '!=', 1)
            ->orWhere('product_supplier.state', 0)
            ->groupBy('suppliers.id')
            ->get();
        
        foreach ($suppliers as $supplier){
            $productOffs = DB::select("select a.product_id, a.region_id from 
                    (select product_id, region_id, supplier_id FROM `product_supplier` WHERE supplier_id = ? AND state = 0 GROUP BY product_id) a
                    left join suppliers b on a.supplier_id = b.id
                     ", [$supplier->id]);

            $productRegions = [];
            foreach ($productOffs as $product) {
                $productRegions[$product->product_id][] = $product->region_id;
            }

            $products = Product::whereIn('id', array_keys($productRegions))->get();

            foreach ($products as $product) {
                foreach ($productRegions[$product->id] as $regionId) {
                    dispatch(new OffProductToMagento($product, 0, $user, $regionId));
                    $productSupplier = ProductSupplier::where('product_id', $product->id)
                        ->where('region_id', $regionId)
                        ->where('supplier_id', $supplier->id)
                        ->first();
                    $productSupplier->status = 0;
                    $productSupplier->state = 0;
                    $productSupplier->save();
                }
            }
        }
    }
}
