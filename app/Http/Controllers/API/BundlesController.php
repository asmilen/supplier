<?php

namespace App\Http\Controllers\API;

use App\Models\BundleProduct;
use DB;
use App\Models\Bundle;
use App\Models\Province;
use App\Models\BundleCategory;
use App\Http\Controllers\Controller;
use App\Models\SupplierSupportedProvince;

class BundlesController extends Controller
{
    public function listBundleByProvinceCode($codeProvince)
    {
        $labels = config('teko.bundleLabels');

        $bundles = Bundle::withCount(['products','categories' => function ($query) {
                $query->where('bundle_category.status',true);
            }])
            ->where('bundles.status',true)
            ->where('region_id', Province::getRegionIdsByCode($codeProvince))
            ->whereIn('label', array_keys($labels))
            ->havingRaw('products_count > 0')
            ->get()
            ->groupBy('label');

        return $bundles->map(function ($bundle, $key) use ($labels) {
            return [
                'title' => $labels[$key],
                'data' => $bundle
            ];
        });
    }

    public function getBundleProduct($bundleId)
    {
        $provinceId = request('province_id');
        try {
            $bundle = Bundle::findOrFail($bundleId);

            $supplierIds = SupplierSupportedProvince::whereIn(
                'province_id', Province::getListByRegion($bundle->region_id)
            )->pluck('supplier_id')->all();

            return BundleCategory::getListByBundleId($bundle->id)->map(function ($bundleCategory) use ($bundle, $supplierIds, $provinceId) {
                return [
                    'title' => $bundleCategory->name,
                    'data' => $bundleCategory->getBundleProducts($supplierIds, $bundle->region_id, $provinceId),
                ];
            });
        } catch (\Exception $e) {
            return api_response()->errorUnprocessableEntity($e->getMessage());
        }
    }
}
