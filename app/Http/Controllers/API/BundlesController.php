<?php

namespace App\Http\Controllers\API;

use DB;
use App\Models\Bundle;
use App\Models\Product;
use App\Models\Province;
use App\Models\ProductSupplier;
use App\Models\MarginRegionCategory;
use App\Models\SupplierSupportedProvince;
use App\Http\Controllers\Controller;
use App\Models\BundleProduct;
use App\Models\BundleCategory;

class BundlesController extends Controller
{
    public function listBundleByProvinceCode($codeProvince,$labelId)
    {
        try {

            $regionId = Province::where('code', $codeProvince)->pluck('region_id');

            $bundles = Bundle::whereIn('region_id',$regionId)->where('label',$labelId)->get();

            return $bundles;

        }
        catch (\Exception $e) {

            return api_response(['message' => $e->getMessage()], 500);
        }

    }

    public function getBundleProduct($bundleId)
    {
        try {
            $regionId =  Bundle::where('id',$bundleId)->pluck('region_id');

            $provinceIds = Province::where('region_id', $regionId)->pluck('id');

            $supplierIds = SupplierSupportedProvince::whereIn('province_id', $provinceIds)
                ->get()
                ->pluck('supplier_id');

            $bundleCategories = BundleCategory::where('id_bundle',$bundleId)->get();

            $response = [];

            foreach ($bundleCategories as $bundleCategory) {

                $bundleProducts = BundleProduct::where('id_bundle',$bundleCategory->id_bundle)
                    ->where('id_bundleCategory',$bundleCategory->id)->get();

                $products = [];
                foreach ($bundleProducts as $bundleProduct) {

                    $product = Product::select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`, `product_supplier`.`image` as `source_url`,`products`.`category_id`"))
                        ->join('product_supplier', function ($q) use ($supplierIds) {
                            $q->on('product_supplier.product_id', '=', 'products.id')
                                ->whereIn('product_supplier.supplier_id', $supplierIds)
                                ->where('product_supplier.state', '=', 1);
                        })
                        ->findOrFail($bundleProduct->id_product);

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

                    $product->quantity = $bundleProduct->quantity;
                    $product->isDefault = $bundleProduct->is_default;

                    array_push($products,$product);
                }

                $bundleCategory = $bundleCategory->name;

                $bundle = [
                    'title' => $bundleCategory,
                    'data' => $products
                ];

                array_push($response,$bundle);
            }
            return $response;
        } catch (\Exception $e) {
            return api_response(['message' => $e->getMessage()], 500);
        }

    }
}
