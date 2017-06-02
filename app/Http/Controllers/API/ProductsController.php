<?php

namespace App\Http\Controllers\API;

use DB;
use Validator;
use Datatables;
use App\Models\Color;
use App\Models\Product;
use App\Models\Category;
use App\Models\Province;
use App\Models\Manufacturer;
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
            'products.category_id', 'product_supplier.supplier_id', 'product_supplier.quantity',
            'product_supplier.image', DB::raw('MIN(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, product_supplier.import_price)) as best_import_price')
            , DB::raw('MIN(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * (1 + 0.01 * IFNULL(margin_region_category.margin,5))/1000) * 1000)) as price')
            , DB::raw('MIN(ceil(product_supplier.import_price/1000) * 1000) as import_price')
        ])
            ->with('category')
            ->join('product_supplier', function ($q) use ($supplierIds) {
                $q->on('product_supplier.product_id', '=', 'products.id')
                    ->whereIn('product_supplier.supplier_id', $supplierIds)
                    ->where('product_supplier.state', '=', 1);
            })
            ->leftJoin('margin_region_category', function ($q) use ($regions) {
                $q->on('margin_region_category.category_id', '=', 'products.category_id')
                    ->whereIn('margin_region_category.region_id', $regions);
            });

        return Datatables::eloquent($model)
            ->setTransformer(new ProductApiTransformer())
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
            // ->addColumn('price', function ($model) use ($regions) {
            // $margin = MarginRegionCategory::where('category_id', $model->category_id)
            // ->whereIn('region_id', $regions)->first();
            // return isset($margin) ? $model->best_import_price * (1 + 0.01 * $margin->margin) : $model->best_import_price * 1.05;
            // })
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
                ->select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`, `product_supplier`.`image` as `source_url`, `products`.`manufacturer_id`, `products`.`category_id`, `product_supplier`.`quantity`"))
                ->join('product_supplier', function ($q) use ($supplierIds) {
                    $q->on('product_supplier.product_id', '=', 'products.id')
                        ->whereIn('product_supplier.supplier_id', $supplierIds)
                        ->where('product_supplier.state', '=', 1);
                })
                ->findOrFail($id);

            $margin = MarginRegionCategory::where('category_id', $product->category_id)
                ->whereIn('region_id', $regions)->first();

            if ($margin) {
                $productMargin = 1 + 0.01 * $margin->margin;
            } else {
                $productMargin = 1.05;
            }

            $product->best_price = ProductSupplier::where('product_id', $id)
                ->whereIn('product_supplier.supplier_id', $supplierIds)
                ->where('product_supplier.state', '=', 1)
                ->min(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * ' . $productMargin . '/1000) * 1000))'));

            $product->import_price = ProductSupplier::where('product_id', $id)
                ->whereIn('product_supplier.supplier_id', $supplierIds)
                ->where('product_supplier.state', '=', 1)
                ->min(DB::raw('ceil(product_supplier.import_price / 1000) * 1000'));

            return $product;
        } catch (\Exception $e) {

            return api_response(['message' => $e->getMessage()], 500);
        }
    }

    public function show(Product $product)
    {
        return $product;
    }

    public function createFromGoogleSheet()
    {
        $results = [];

        foreach (request()->all() as $productData) {
            try {
                $this->createProductFromGoogleSheetData($productData);

                array_push($results, ['Nhập thành công.']);
            } catch (\Exception $e) {
                array_push($results, ['Lỗi: '.$e->getMessage()]);
            }
        }

        return response()->json($results);
    }

    protected function createProductFromGoogleSheetData($productData)
    {
        $category = Category::where('name', $productData['category_name'])->firstOrFail();

        $manufacturer = Manufacturer::where('name', $productData['manufacturer_name'])->firstOrFail();

        $color = Color::where('name', $productData['color'])->first();

        $product = Product::where('name', $productData['name'])->first();

        if ($product) {
            throw new \Exception('Sản phẩm đã tồn tại.');
        }

        if (! empty($productData['code'])) {
            $check = Product::where('category_id', $category->id)
                ->where('manufacturer_id', $manufacturer->id)
                ->where('code', $productCode)
                ->first();

            if ($check) {
                throw new \Exception('Mã sản phẩm đã tồn tại.');
            }
        }

        $product = Product::forceCreate([
            'category_id' => $category->id,
            'manufacturer_id' => $manufacturer->id,
            'color_id' => $color ? $color->id : 0,
            'name' => $productData['name'],
            'status' => 0,
        ]);

        if (empty($productCode)) {
            $productCode = $product->id;
        }

        $product->forceFill([
            'code' => $productCode,
            'sku' => generate_sku($category->code, $manufacturer->code, $productCode, $color ? $color->code : ''),
        ])->save();

        return true;
    }
}
