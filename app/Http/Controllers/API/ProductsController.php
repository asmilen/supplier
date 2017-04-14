<?php

namespace App\Http\Controllers\API;

use Validator;
use Datatables;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Models\SupplierSupportedProvince;
use App\Transformers\ProductApiTransformer;

class ProductsController extends Controller
{
    public function index()
    {
        /**
         * @var \Illuminate\Validation\Validator $validator
         */
        $validator = Validator::make(request()->all(), [
            'province_ids' => 'required|array',
            'province_ids.*' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }
        /**
         * @var array $supplierIds
         */
        $supplierIds = SupplierSupportedProvince::whereIn('province_id', request('province_ids'))
            ->get()
            ->pluck('supplier_id');

        $model = Product::select([
            'products.id', 'products.name', 'products.code',
            'products.sku', 'products.source_url', 'products.best_price',
            'products.category_id'
        ])
            ->with('category')
            ->join('product_supplier', function ($q) use ($supplierIds) {
                $q->on('product_supplier.product_id', '=', 'products.id')
                    ->whereIn('product_supplier.supplier_id', $supplierIds);
            });

        return Datatables::eloquent($model)
            ->setTransformer(new ProductApiTransformer())
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
            ->addColumn('price', function ($model) {
                return $model->best_price * (1 + 0.01 * $model->category->margin);
            })
            ->groupBy('products.id', 'products.name', 'products.code',
                'products.sku', 'products.source_url', 'products.best_price',
                'products.category_id')
            ->make(true);
    }

    /**
     * @param int $id
     * @return Product
     */
    public function detail($id)
    {
        try {
            $product = Product::with('manufacturer', 'category')->findOrFail($id);
            if (isset($product->category->margin)) {
                $product->best_price = $product->best_price * (1 + 0.01 * $product->category->margin);
            }
            return $product;
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
