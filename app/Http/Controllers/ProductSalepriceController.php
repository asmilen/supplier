<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Product;

class ProductSalepriceController extends Controller
{
    public function show(Product $product)
    {
        return view('products.saleprice.show', compact('product'));
    }

    public function update(Product $product)
    {
        Validator::make(request()->all(), [
            'price' => 'required',
        ])->after(function ($validator) {
            if (request('price') <= 0) {
                $validator->errors()->add('price', 'Giá bán phải > 0.');
            }

            $hasStore = false;

            foreach (request('stores') as $storeId => $flag) {
                if ($flag) {
                    $hasStore = true;
                }
            }

            if (! $hasStore) {
                $validator->errors()->add('stores', 'Cần chọn ít nhất 1 store để áp dụng giá bán.');
            }
        })->validate();
        return $product;
    }
}
