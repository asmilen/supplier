<?php

namespace App\Http\Controllers\API;

use App\Models\Supplier;
use DB;
use Validator;
use Datatables;
use App\Models\Color;
use App\Models\Product;
use App\Models\Category;
use App\Models\Province;
use App\Models\TransportFee;
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
            'products.sku', 'products.source_url', 'products.best_price', 'products.category_id',
            'products.manufacturer_id', 'product_supplier.supplier_id', 'product_supplier.quantity',
            'products.image',
            DB::raw('(ceil(product_supplier.import_price / 1000) * 1000) as import_price'),
            DB::raw('(ceil(product_supplier.import_price * (1 + 0.01 * IFNULL(margin_region_category.margin,5))/1000) * 1000) as import_price_w_margin')
            , DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price/1000) * 1000)) as price')
            , DB::raw('if((if(product_supplier.price_recommend > 0, product_supplier.price_recommend, 10000000000)) = 10000000000, 0 ,
								(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, 10000000000))) as recommended_price')
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
            })
            ->where('products.status', 1);

        return Datatables::eloquent($model)
            ->setTransformer(new ProductApiTransformer($provinceIds, request('province_ids')))
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

                if (request()->has('from_price') && request('from_price')) {
                    $query->having('price', '>=', request('from_price'));
                }

                if (request()->has('to_price') && request('to_price')) {
                    $query->having('price', '<=', request('to_price'));
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
            ->active()
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
                ->select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`, `products`.`image` as `source_url`, `products`.`manufacturer_id`, `products`.`category_id`, `product_supplier`.`quantity`"))
                ->leftJoin('product_supplier', function ($q) use ($supplierIds) {
                    $q->on('product_supplier.product_id', '=', 'products.id')
                        ->whereIn('product_supplier.supplier_id', $supplierIds)
                        ->where('product_supplier.state', '=', 1);
                })
                ->findOrFail($id);

            $margin = MarginRegionCategory::where('category_id', $product->category_id)
                ->whereIn('region_id', $regions)->first();

            $province = Province::whereIn('code', request('province_ids'))->pluck('id');
            $provinceFee = TransportFee::whereIn('province_id', $province)
                ->orderBy('percent_fee')
                ->first();
            $minPrice = ProductSupplier::where('product_id', $id)
                ->whereIn('product_supplier.supplier_id', $supplierIds)
                ->leftJoin('supplier_supported_province', 'product_supplier.supplier_id', '=', 'supplier_supported_province.supplier_id')
                ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'supplier_supported_province.province_id')
                ->where('product_supplier.state', '=', 1)
                ->orderBy('product_supplier.import_price')
                ->orderBy('transport_fees.percent_fee')
                ->first();

            $provinceFeeMin = SupplierSupportedProvince::with('transportFee')
                ->where('supplier_id', $minPrice ? $minPrice->supplier->id : 0)
                ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'supplier_supported_province.province_id')
                ->orderBy('transport_fees.percent_fee')
                ->first();

                $ship_province = $province->toArray();
                if (in_array($provinceFeeMin->transportFee ? $provinceFeeMin->transportFee->province_id : 0, $ship_province)) {
                    $productMargin = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01;
                } else {
                    $productMargin = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01 + ($provinceFeeMin->transportFee ? $provinceFeeMin->transportFee->percent_fee : 0) * 0.01;
                }
            $w_margin = ($margin ? $margin->margin : 5) * 0.01 ;

            $product->best_price = ProductSupplier::where('product_id', $id)
                ->whereIn('product_supplier.supplier_id', $supplierIds)
                ->where('product_supplier.state', '=', 1)
                ->min(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * ' . $productMargin . '/1000) * 1000))'));
            $product->import_price = ProductSupplier::where('product_id', $id)
                ->whereIn('product_supplier.supplier_id', $supplierIds)
                ->where('product_supplier.state', '=', 1)
                ->min(DB::raw('ceil(product_supplier.import_price * (' . $productMargin . '-' . $w_margin . ')/1000) * 1000'));

            $product->import_price_w_margin = ProductSupplier::where('product_id', $id)
                ->whereIn('product_supplier.supplier_id', $supplierIds)
                ->where('product_supplier.state', '=', 1)
                ->min(DB::raw('ceil(product_supplier.import_price * ' . $productMargin . '/1000) * 1000'));

            $product->recommended_price = ProductSupplier::where('product_id', $id)
                ->whereIn('product_supplier.supplier_id', $supplierIds)
                ->where('product_supplier.state', '=', 1)
                ->where('price_recommend', $product->best_price)
                ->min('product_supplier.price_recommend');

            if ($product->recommended_price == $product->best_price) {
                $suppliers = ProductSupplier::where('price_recommend', $product->best_price)
                    ->where('product_id', $id)
                    ->leftJoin('suppliers', 'product_supplier.supplier_id', '=', 'suppliers.id')
                    ->pluck('supplier_id');
                $supplier = Supplier::whereIn('id', $suppliers ? $suppliers : 0)
                    ->get();
                $province = SupplierSupportedProvince::whereIn('supplier_id', $suppliers ? $suppliers : 0)
                        ->leftJoin('provinces', 'supplier_supported_province.province_id', '=', 'provinces.id')
                        ->get();
                $product->import_price = ProductSupplier::where('product_id', $id)
                    ->whereIn('product_supplier.supplier_id', $suppliers)
                    ->where('product_supplier.state', '=', 1)
                    ->min(DB::raw('ceil(product_supplier.import_price * (' . $productMargin . '-' . $w_margin . ')/1000) * 1000'));

                $product->import_price_w_margin = ProductSupplier::where('product_id', $id)
                    ->whereIn('product_supplier.supplier_id', $suppliers)
                    ->where('product_supplier.state', '=', 1)
                    ->min(DB::raw('ceil(product_supplier.import_price * ' . $productMargin . '/1000) * 1000'));
            } else {
                $supplier = Supplier::where('id', $provinceFeeMin ? $provinceFeeMin->supplier_id : 0)
                    ->get();
                $province = Province::where('id', $provinceFeeMin ? $provinceFeeMin->province_id : 0)
                    ->get();
            }
            for ($i = 0; $i < $supplier->count(); $i++) {
                $product->suppliers = array_merge([[
                    'id' => isset($supplier[$i]) ? $supplier[$i]->id : null,
                    'name' => isset($supplier[$i]) ? $supplier[$i]->name : null,
                    'import_price' => ProductSupplier::where('product_id', $id)
                        ->where('product_supplier.supplier_id', $supplier[$i]->id)
                        ->where('product_supplier.state', '=', 1)
                        ->min(DB::raw('ceil(product_supplier.import_price / 1000) * 1000')),
                    'province_name' => isset($province[$i]) ? $province[$i]->name : null,
                    'province_code' => isset($province[$i]) ? $province[$i]->code : null
                ]], is_array($product->suppliers) ? $product->suppliers : []);
            }

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
                $product = $this->createProductFromGoogleSheetData($productData);

                array_push($results, [$product->sku, 'Nhập thành công.']);
            } catch (\Exception $e) {
                array_push($results, ['', 'Lỗi: ' . $e->getMessage()]);
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
            return $product;
        }

        if (!empty($productData['code'])) {
            $check = Product::where('category_id', $category->id)
                ->where('manufacturer_id', $manufacturer->id)
                ->where('code', $productData['code'])
                ->first();

            if ($check) {
                throw new \Exception('Mã sản phẩm đã tồn tại.');
            }
        }

        return Product::forceCreate([
            'category_id' => $category->id,
            'manufacturer_id' => $manufacturer->id,
            'color_id' => $color ? $color->id : 0,
            'name' => $productData['name'],
            'code' => $productData['code'],
            'status' => 0,
        ]);
    }

    public function getConfigurableList()
    {
        return Product::where('type', 1)->get();
    }

    public function getMinImportPriceList()
    {
        $products = ProductSupplier::getListByRegion(request('region_id', 0))
            ->map(function ($productSuppliers, $productId) {
                $product = $productSuppliers->shift();

                $productSuppliers->each(function ($productSupplier) use (&$product) {
                    if ($productSupplier->state == 1 && $productSupplier->import_price < $product->import_price) {
                        $product->import_price = $productSupplier->import_price;
                    }
                });

                return [
                    'id' => $product->product_id,
                    'sku' => $product->sku,
                    'min_import_price' => $product->import_price,
                ];
            });

        return response()->json($products);
    }

    public function deactivateErrorsFromGoogleSheet()
    {
        foreach (request()->all() as $productData) {
            try {
                $product = Product::findOrFail($productData['id']);

                $product->forceFill([
                    'name' => $productData['name'],
                    'status' => false,
                ])->save();
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
            }
        }
    }

    public function getProductQuotation(){
        $query = 'select p.id,p.sku,p.name,c.name as cname,m.name as mname,ps.supplier_name,ps.import_price,"" as location,ps.updated_at from products as p
                    left join categories as c on p.category_id = c.id
                    left join manufacturers as m on p.manufacturer_id = m.id
                    inner join (select ps.*,s.name as supplier_name from product_supplier as ps
                    left join suppliers as s on ps.supplier_id = s.id
                    where ps.state = 1
                    ) as ps on p.id = ps.product_id
                    where p.status = 1
                    order by ps.supplier_name asc
                    limit :limit offset :offset' ;

        $products = DB::select($query,['limit' => request('limit'),'offset' => request('offset')]);

        return $products;
    }

    public function getProductWithCategoryManufacturer(){

        $products = Product::active()
            ->with('category')
            ->with('manufacturer')
            ->skip(request('offset'))->take(request('limit'))
            ->get();

        return $products;
    }

    public function search()
    {
        $page = request('page', 1);

        $limit = request('limit', 10);

        $offset = ($page - 1) * $limit;

        $builder = Product::active()
            ->where(function ($query) {
                $q = request('q');

                $query->where('id', $q)
                    ->orWhere('sku', 'like', '%'.$q.'%');
            });

        $totalItems = $builder->count();

        $products = $builder->skip($offset)
            ->take($limit)
            ->get();

        return response()->json([
            'data' => $products,
            'total_items' => $totalItems,
        ]);
    }
}
