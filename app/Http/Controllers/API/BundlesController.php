<?php

namespace App\Http\Controllers\API;

use DB;
use App\Models\Bundle;
use App\Models\Province;
use App\Models\Product;
use App\Models\SupplierSupportedProvince;
use App\Http\Controllers\Controller;
use App\Models\BundleProduct;
use GuzzleHttp\Psr7\Request;

class BundlesController extends Controller
{
    public function getBundleProductByRegionId($regionId)
    {
        $bundleIds = Bundle::where('region_id',$regionId)->pluck('id');

        $provinceIds = Province::where('region_id', $regionId)->pluck('id');

        $bundleProductIds = BundleProduct::whereIn('id_bundle',$bundleIds)->pluck('id_product');

        $supplierIds = SupplierSupportedProvince::whereIn('province_id', $provinceIds)
            ->get()
            ->pluck('supplier_id');

        $products = Product::with('manufacturer', 'category')
            ->select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`, `product_supplier`.`image` as `source_url`, `products`.`manufacturer_id`, `products`.`category_id`, `product_supplier`.`quantity`"))
            ->join('product_supplier', function ($q) use ($supplierIds) {
                $q->on('product_supplier.product_id', '=', 'products.id')
                    ->whereIn('product_supplier.supplier_id', $supplierIds)
                    ->where('product_supplier.state', '=', 1);
            })
            ->whereIn('products.id',$bundleProductIds)->get();

        $response = [];
        foreach ($products as $product) {

            $margin = MarginRegionCategory::where('category_id', $product->category_id)
                ->where('region_id', $regionId)->first();

            if ($margin) {
                $productMargin = 1 + 0.01 * $margin->margin;
            } else {
                $productMargin = 1.05;
            }

            $product->best_price = ProductSupplier::where('product_id', $product->id)
                ->whereIn('product_supplier.supplier_id', $supplierIds)
                ->min(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, product_supplier.import_price * ' . $productMargin . '))'));

            array_push($response,$product);
        }

        return $response;
    }
}
