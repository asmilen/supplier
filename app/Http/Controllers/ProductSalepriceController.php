<?php

namespace App\Http\Controllers;

use App\Models\ProductSupplier;
use Validator;
use App\Models\Product;
use App\Models\Saleprice;

class ProductSalepriceController extends Controller
{
    public function show(Product $product)
    {
        $productSuppliers = ProductSupplier::where('product_id', $product->id)
                                ->orderBy('import_price')
                                ->take(5)
                                ->get();

        return view('products.saleprice.show', compact('product', 'productSuppliers'));
    }

    public function update(Product $product)
    {
        Validator::make(request()->all(), [
            'price' => 'required|numeric',
            'stores.*' => 'required',
        ])->after(function ($validator) {
            if (request('price') <= 0) {
                $validator->errors()->add('price', 'Giá bán phải > 0.');
            }
            if (!in_array(true,request('stores'))) {
                $validator->errors()->add('stores', 'Bạn phải chọn ít nhất 1 store.');
            }
            if (!in_array(true,request('regions'))) {
                $validator->errors()->add('regions', 'Bạn phải chọn ít nhất 1 miền.');
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
