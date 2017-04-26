<?php

namespace App\Http\Controllers\API;

use DB;
use Validator;
use Datatables;
use App\Models\Product;
use App\Models\Province;
use App\Models\ProductSupplier;
use App\Models\MarginRegionCategory;
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
            'province_ids.*' => 'required',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $regions = Province::whereIn('code', request('province_ids'))->pluck('region_id');

        $provinceIds = Province::whereIn('region_id', $regions)->pluck('id');

        /**
         * @var array $supplierIds
         */
        $supplierIds = SupplierSupportedProvince::whereIn('province_id', $provinceIds)
            ->get()
            ->pluck('supplier_id');


        $model = Product::select([
            'products.id', 'products.name', 'products.code',
            'products.sku', 'products.source_url', 'products.best_price',
            'products.category_id', 'product_supplier.supplier_id',
            'product_supplier.image', DB::raw('MIN(product_supplier.import_price) as best_import_price')
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

                if (request()->has('category_id') && request('category_id')) {
                    $query->where('products.category_id', request('category_id'));
                }

                if (request()->has('manufacturer_id') && request('manufacturer_id')) {
                    $query->where('products.manufacturer_id', request('manufacturer_id'));
                }
            })
            ->addColumn('price', function ($model) use ($regions) {
                $margin = MarginRegionCategory::where('category_id', $model->category_id)
                    ->whereIn('region_id', $regions)->first();
                return isset($margin) ? $model->best_import_price * (1 + 0.01 * $margin->margin) : $model->best_import_price * 1.05;
            })
            ->groupBy('products.id', 'products.name', 'products.code',
                'products.sku', 'products.source_url', 'products.best_price',
                'products.category_id')
            ->make(true);
    }

    public function getListProductSku()
    {
        /**
         * @var array $supplierIds
         */
        $model = Product::select(['products.id', 'products.name', 'products.sku', 'products.category_id'])
            ->with('category');

        return Datatables::eloquent($model)
            ->filter(function ($query) {
                if (request()->has('sku')) {
                    $query->where('products.sku', 'like', '%' . request('sku') . '%');
                }
                if (request()->has('name')) {
                    $query->where('products.name', 'like', '%' . request('name') . '%');
                }

                if (request()->has('category_id') && request('category_id')) {
                    $query->where('products.category_id', request('category_id'));
                }

                if (request()->has('manufacturer_id') && request('manufacturer_id')) {
                    $query->where('products.manufacturer_id', request('manufacturer_id'));
                }
            })
            ->make(true);
    }

    /**
     * @param int $id
     * @return Product
     */
    public function detail($id)
    {
        /**
         * @var \Illuminate\Validation\Validator $validator
         */
        $validator = Validator::make(request()->all(), [
            'province_ids' => 'required|array',
            'province_ids.*' => 'required',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        try {
            
            $regions = Province::whereIn('code', request('province_ids'))->pluck('region_id');

            $provinceIds = Province::whereIn('region_id', $regions)->pluck('id');

            /**
             * @var array $supplierIds
             */
            $supplierIds = SupplierSupportedProvince::whereIn('province_id', $provinceIds)
                ->get()
                ->pluck('supplier_id');

            $product = Product::with('manufacturer', 'category')
                ->select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`, `product_supplier`.`image` as `source_url`, `products`.`manufacturer_id`, `products`.`category_id`"))
                ->join('product_supplier', function ($q) use ($supplierIds) {
                    $q->on('product_supplier.product_id', '=', 'products.id')
                        ->whereIn('product_supplier.supplier_id', $supplierIds);
                })
                ->findOrFail($id);

            $bestPrice = ProductSupplier::where('product_id', $id)
                ->whereIn('product_supplier.supplier_id', $supplierIds)
                ->min('import_price');

            $margin = MarginRegionCategory::where('category_id', $product->category_id)
                ->whereIn('region_id', $regions)->first();

            if ($margin) {
                $product->best_price = $bestPrice * (1 + 0.01 * $margin->margin);
            } else {
                $product->best_price = $bestPrice * 1.05;
            }

            return $product;
        } catch (\Exception $e) {

            return api_response(['message' => $e->getMessage()], 500);
        }
    }
}
