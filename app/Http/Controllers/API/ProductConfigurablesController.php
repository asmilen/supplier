<?php

namespace App\Http\Controllers\API;

use DB;
use Validator;
use Datatables;
use App\Models\Product;
use App\Http\Controllers\Controller;

class ProductConfigurablesController extends Controller
{
    public function index()
    {
        $model = Product::with('children')->where('type', 1);

        return Datatables::eloquent($model)
            ->filter(function ($query) {
                if (request()->has('name')) {
                    $query->where('products.name', 'like', '%' . request('name') . '%');
                }
                if (request()->has('category_id')) {
                    $query->where('products.category_id', request('category_id'));
                }
                if (request()->has('manufacturer_id')) {
                    $query->where('products.manufacturer_id', request('manufacturer_id'));
                }
            })
            ->make(true);

    }
}
