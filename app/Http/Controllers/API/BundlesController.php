<?php

namespace App\Http\Controllers\API;

use App\Models\Bundle;
use App\Http\Controllers\Controller;
use App\Models\BundleProduct;
use GuzzleHttp\Psr7\Request;

class BundlesController extends Controller
{
    public function getBundleProductByRegionId($regionId)
    {
        $bundleIds = Bundle::where('region_id',$regionId)->pluck('id');
        $bundleProducts = BundleProduct::join('products','bundle_product.id_product', '=', 'products.id')
            ->select('is_default','quantity','products.name as name','products.price as price')->get();
        dd($bundleProducts);
    }
}
