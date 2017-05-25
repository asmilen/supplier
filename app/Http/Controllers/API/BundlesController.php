<?php

namespace App\Http\Controllers\API;

use DB;
use App\Models\Bundle;
use App\Models\Product;
use App\Models\Province;
use App\Models\BundleProduct;
use App\Models\BundleCategory;
use App\Models\ProductSupplier;
use App\Http\Controllers\Controller;
use App\Models\MarginRegionCategory;
use App\Models\SupplierSupportedProvince;

class BundlesController extends Controller
{
    public function listBundleByProvinceCode($codeProvince)
    {
        $labels = config('teko.bundleLabels');

        $bundles = Bundle::where(
            'region_id', Province::getRegionIdsByCode($codeProvince)
        )->whereIn('label', array_keys(config('teko.bundleLabels')))->get()->groupBy('label');

        return $bundles->mapWithKeys(function ($bundle, $key) use ($labels) {
            return [$labels[$key] => $bundle];
        });
    }

    public function getBundleProduct($bundleId)
    {
        try {
            $bundle = Bundle::findOrFail($bundleId);

            $supplierIds = SupplierSupportedProvince::whereIn(
                'province_id', Province::getListByRegion($bundle->region_id)
            )->pluck('supplier_id')->all();

            return BundleCategory::getListByBundleId($bundle->id)->map(function ($bundleCategory) use ($bundle, $supplierIds) {
                return [
                    'title' => $bundleCategory->name,
                    'data' => $bundleCategory->getBundleProducts($supplierIds, $bundle->region_id),
                ];
            });
        } catch (\Exception $e) {
            return api_response()->errorUnprocessableEntity($e->getMessage());
        }
    }
}
