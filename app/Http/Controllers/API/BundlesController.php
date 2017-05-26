<?php

namespace App\Http\Controllers\API;

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

        $bundles = Bundle::where(
            'region_id', Province::getRegionIdsByCode($codeProvince)
        )->whereIn('label', array_keys($labels))->get()->groupBy('label');


       return $bundles->map(function ($bundle, $key) use ($labels) {
            $data = [];
            foreach ($bundle as $value) {
                $temp = clone $value;
                $count = $value->products->count();
                if ($count > 0) {
                    array_push($data,$temp);
                }
            }
            return [
                'title' => $labels[$key],
                'data' => $data
            ];
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
