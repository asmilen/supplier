<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductAttributesController extends Controller
{
    public function update(Product $product)
    {
        return $product->updateAttributes(request('values', []));
    }
}
