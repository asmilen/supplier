<?php

namespace App\Http\Controllers;

use App\Models\ProductSupplier;
use Validator;
use App\Models\Product;
use App\Models\Saleprice;
use DB;
use App\Models\Province;
use App\Models\SupplierSupportedProvince;
use App\Models\MarginRegionCategory;

class ProductSalepriceController extends Controller
{
    public function show(Product $product)
    {
        $productSuppliers = ProductSupplier::where('product_id', $product->id)
            ->orderBy('import_price')
            ->take(5)
            ->get();

        $nowSalePrices = Saleprice::whereIn('id',
            Saleprice::select(DB::raw('MAX(id) as id_p'))
                    ->where('product_id', $product->id)
                    ->groupBy('store_id', 'region_id')
                    ->get()
            )->get()->sortBy('region_id')->groupBy('region_id');

        $productMarket = DB::table('product_marketprice_best')->where('product_id', $product->id)->first();

        return view('products.saleprice.show', compact('product', 'productSuppliers', 'nowSalePrices', 'productMarket'));
    }

    public function update(Product $product)
    {
        Validator::make(request()->all(), [
            'price' => 'required|numeric',
            'stores.*' => 'required',
        ])->after(function ($validator) use ($product) {
            if (request('price') <= 0) {
                $validator->errors()->add('price', 'Giá bán phải > 0.');
            }
            if (!in_array(true,request('stores'))) {
                $validator->errors()->add('stores', 'Bạn phải chọn ít nhất 1 store.');
            }
            if (!in_array(true,request('regions'))) {
                $validator->errors()->add('regions', 'Bạn phải chọn ít nhất 1 miền.');
            }
            foreach (request('regions') as $regionId => $flagRegion) {
                if ($flagRegion) {
                    $provinceIds = Province::where('region_id', $regionId)->pluck('id');

                    $supplierIds = SupplierSupportedProvince::whereIn('province_id', $provinceIds)
                        ->get()
                        ->pluck('supplier_id');

                    $margin = MarginRegionCategory::where('category_id', $product->category_id)
                        ->where('region_id', $regionId)->first();

                    if ($margin) {
                        $productMargin = 1 + 0.01 * $margin->margin;
                    } else {
                        $productMargin = 1.05;
                    }

                    $minPrice = ProductSupplier::where('product_id', $product->id)
                        ->whereIn('product_supplier.supplier_id', $supplierIds)
                        ->min(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * ' . $productMargin . '/1000) * 1000))'));
                    if (request('price') < $minPrice) {
                        $validator->errors()->add('price', 'Giá bán không hợp lệ cho ' . config('teko.regions')[$regionId]);
                    }
                }
            }
        })->validate();

        foreach (request('stores') as $storeId => $flagStore) {
            if ($flagStore) {
                foreach (request('regions') as $regionId => $flagRegion)
                {
                    if ($flagRegion) {
                        try {
                            $product->addSaleprice([
                                'store_id' => $storeId,
                                'region_id' => $regionId,
                                'price' => request('price'),
                            ]);
                        } catch (\Exception $e) {
                            return response()->json([
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }
        }

        return $product;
    }
}
