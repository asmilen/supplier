<?php

namespace App\Http\Controllers\API;

use Validator;
use Datatables;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Models\SupplierSupportedProvince;

class ProductsController extends Controller
{
    public function index()
    {
        /**
         * @var \Illuminate\Validation\Validator $validator
         */
        $validator = Validator::make(request()->all(), [
            'province_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $supplierIds = SupplierSupportedProvince::whereProvinceId(request('province_id'))->get()->pluck('supplier_id');

        $model = Product::select([
            'products.id', 'products.name', 'products.code', 'products.sku',
        ])->join('product_supplier', function ($q) use ($supplierIds) {
            $q->on('product_supplier.product_id', '=', 'products.id')
                ->whereIn('product_supplier.supplier_id', $supplierIds);
        });

        return Datatables::eloquent($model)
            ->filter(function ($query) {
                if (request()->has('category_id')) {
                    $query->where('category_id', request('category_id'));
                }

                if (request()->has('manufacturer_id')) {
                    $query->where('manufacturer_id', request('manufacturer_id'));
                }
            })
            ->groupBy('products.id' , 'products.name' , 'products.code' , 'products.sku')
            ->make(true);
    }
}
