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
        })->validate();

        foreach (request('stores') as $storeId => $flag) {
            if ($flag) {
                try {
                    $product->addSaleprice([
                        'store_id' => $storeId,
                        'price' => request('price'),
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $product;
    }
}