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
            , DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * (1 + 0.01 * IFNULL(margin_region_category.margin,5))/1000) * 1000)) as price')
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
            ->where('products.channel', 'like', '%' . 2 . '%')
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
            $regions = Province::where('code', request('province_ids'))->pluck('region_id'); // tìm miền của tỉnh mua hàng
            $provinceIds = Province::where('region_id', $regions)->pluck('id'); // tìm các tỉnh thuộc miền

            /**
             * @var array $supplierIds
             */
            $supplierIds = SupplierSupportedProvince::whereIn('province_id', $provinceIds)
                ->leftJoin('suppliers', 'suppliers.id', 'supplier_supported_province.supplier_id')
                ->where('suppliers.status', 1)
                ->pluck('supplier_id'); // tìm tất cả các nhà cung cấp cung cấp cho miền của người mua hàng

            $product = Product::with('manufacturer', 'category')
                ->select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`,  `products`.`description`, `products`.`image` as `source_url`, `products`.`manufacturer_id`, `products`.`category_id`, `product_supplier`.`quantity`"))
                ->leftJoin('product_supplier', function ($q) use ($supplierIds) {
                    $q->on('product_supplier.product_id', '=', 'products.id')
                        ->whereIn('product_supplier.supplier_id', $supplierIds)
                        ->where('product_supplier.state', '=', 1);
                })
                ->where('products.channel', 'like', '%' . 2 . '%')
                ->findOrFail($id); // kiểm tra thông tin sản phẩm cần mua

            $margin = MarginRegionCategory::where('category_id', $product->category_id)
                ->whereIn('region_id', $regions)->first(); // tính margin của category sản phẩm

            $marginValue = ($margin ? 1 + 0.01 * $margin->margin : 1.05); // giá trị của margin

            $province = Province::whereIn('code', request('province_ids'))->pluck('id'); // tìm tỉnh mua hàng

            $provinceFee = TransportFee::where('province_id', $province ? $province[0] : 0)->first();// phí ship của tỉnh mua hàng

            $feeValue = ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01; //giá trị của phí ship của tỉnh mua hàng

            $minPrice = ProductSupplier::where('product_id', $id)
                ->whereIn('product_supplier.supplier_id', $supplierIds)
                ->leftJoin('supplier_supported_province', 'product_supplier.supplier_id', '=', 'supplier_supported_province.supplier_id')
                ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'supplier_supported_province.province_id')
                ->where('product_supplier.state', '=', 1)
                ->orderBy(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, product_supplier.import_price * (' .
                    $marginValue . '+' . $feeValue . '+' . '(case when supplier_supported_province.province_id = ' . $province[0]  . ' then 0 else if(transport_fees.percent_fee is null, 0,transport_fees.percent_fee) end ))))'))
                ->orderBy('transport_fees.percent_fee')
                ->first(); // giá tốt nhất tìm được trong miền sau khi cộng margin và fee

            if ($minPrice){
                $provinceFeeMin = SupplierSupportedProvince::with('transportFee')
                    ->where('supplier_id', $minPrice ? $minPrice->supplier->id : 0)
                    ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'supplier_supported_province.province_id')
                    ->orderBy('transport_fees.percent_fee')
                    ->first(); // lấy phí vận chuyển thấp nhất của ncc cung cấp sản phẩm với giá tốt nhất

                $supportedProvince = SupplierSupportedProvince::where('supplier_id', $minPrice->supplier ? $minPrice->supplier->id : 0)->where('status', 1)->pluck('province_id');
                //kiểm tra nhà cung cấp sản phẩm có hỗ trợ cho tỉnh mua hàng ko

                if (in_array($province[0], $supportedProvince ? $supportedProvince->toArray() : [])) {
                    $productMargin = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01;
                } else {
                    $productMargin = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01 + ($provinceFeeMin->transportFee ? $provinceFeeMin->transportFee->percent_fee : 0) * 0.01;
                }
                $w_margin = ($margin ? $margin->margin : 5) * 0.01;

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
                        ->where('status', 1)
                        ->get();
                    $province = SupplierSupportedProvince::whereIn('supplier_id', $suppliers ? $suppliers : 0)
                        ->leftJoin('provinces', 'supplier_supported_province.province_id', '=', 'provinces.id')
                        ->get();
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
            }else{
                return api_response(['message' => 'Sản phẩm không tồn tại hoặc không có nhà cung cấp'], 404);
            }

        } catch (\Exception $e) {
            return api_response(['message' => $e->getMessage()], 500);
        }
    }

    public function show(Product $product)
    {
        $product->product_options = config('teko.product.channel', []);
        $product->channels = explode(",", $product->channel);
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

    public function updateNameBySku()
    {
        $results = [];
        foreach (request('form_data') as $productData) {
            try {
                $product = Product::where('sku',$productData['sku'])->first();

                if ($product)
                {
                    $product->name = $productData['name'];
                    $product->save();
                    array_push($results, ['Cập nhật thành công.']);
                } else {
                    array_push($results, ['Lỗi: Sku ko tồn tại']);
                }

            } catch (\Exception $e) {
                array_push($results, ['Lỗi: ' . $e->getMessage()]);
            }
        }

        return response()->json($results);
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

    public function getProductQuotation()
    {
        $isIncludeOutStock = request('include_out_stock');

        $query = 'select p.id,p.sku,p.name,c.name as cname,m.name as mname,ps.supplier_name,ps.import_price,ps.price_recommend,ps.updated_at from products as p
                    left join categories as c on p.category_id = c.id
                    left join manufacturers as m on p.manufacturer_id = m.id
                    inner join (select ps.*,s.name as supplier_name from product_supplier as ps
                    left join suppliers as s on ps.supplier_id = s.id ' .
            (!$isIncludeOutStock ? 'where ps.state = 1' : '') .
            ') as ps on p.id = ps.product_id
                    where p.status = 1
                    order by ps.supplier_name asc
                    limit :limit offset :offset';

        $products = DB::select($query, ['limit' => request('limit'), 'offset' => request('offset')]);

        return $products;
    }

    public function getProductWithCategoryManufacturer()
    {

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
                    ->orWhere('sku', 'like', '%' . $q . '%');
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
