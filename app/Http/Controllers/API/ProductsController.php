<?php

namespace App\Http\Controllers\API;

use DB;
use Validator;
use Datatables;
use App\Models\Color;
use App\Models\Product;
use App\Models\Category;
use App\Models\Province;
use App\Models\Supplier;
use App\Models\Manufacturer;
use App\Models\TransportFee;
use App\Events\ProductUpserted;
use App\Models\ProductSupplier;
use App\Http\Controllers\Controller;
use App\Models\MarginRegionCategory;
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

        $regionIds = Province::whereIn('code', request('province_ids'))->pluck('region_id');

        if (!$regionIds) {
            return api_response(['message' => 'Mã tỉnh thành không tồn tại'], 404);
        }

        $provinceIds = Province::whereIn('region_id', $regionIds)->pluck('id');

        /**
         * @var array $supplierIds
         */

        $province = Province::whereIn('code', request('province_ids'))->pluck('id'); // tìm tỉnh mua hàng

        $provinceFee = TransportFee::where('province_id', $province ? $province[0] : 0)->first();// phí ship của tỉnh mua hàng

        $feeValue = ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01; //giá trị của phí ship của tỉnh mua hàng

        $model = Product::select([
            'products.id', 'products.name', 'products.sku', 'products.image as source_url', 'products.image', 'products.category_id'
            , DB::raw('MIN(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * (1 + 0.01 * IFNULL(margin_region_category.margin,5) + ' . $feeValue . '+ (case when supplier_supported_province.province_id = ' . $province[0] . ' then 0 else if(transport_fees.percent_fee is null, 0,transport_fees.percent_fee/100) end ))/1000) * 1000)) as price')
            , DB::raw('if(MIN(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, 10000000000)) = 10000000000, 0 ,
								MIN(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, 10000000000))) as recommended_price')
        ])
//            ->with('category')
            ->join('product_supplier', function ($q) use ($regionIds) {
                $q->on('product_supplier.product_id', '=', 'products.id')
                    ->whereIn('product_supplier.region_id', $regionIds)
                    ->where('product_supplier.state', '=', 1);
            })
            ->join('suppliers', function ($q) {
                $q->on('product_supplier.supplier_id', '=', 'suppliers.id')
                    ->where('suppliers.status', '=', 1);
            })
            ->leftJoin('margin_region_category', function ($q) use ($regionIds) {
                $q->on('margin_region_category.category_id', '=', 'products.category_id')
                    ->whereIn('margin_region_category.region_id', $regionIds);
            })
            ->join('supplier_supported_province', 'product_supplier.supplier_id', '=', 'supplier_supported_province.supplier_id')
            ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'supplier_supported_province.province_id')
            ->where('products.status', 1);

        return Datatables::eloquent($model)
            ->setTransformer(new ProductApiTransformer($provinceIds, request('province_ids'), $provinceFee, $feeValue))
            ->filter(function ($query) {
                if (request()->has('sku')) {
                    $query->where('products.sku', 'like', '%' . request('sku') . '%');
                }

                $channels = request('channels', [2]);
                $query->where(function ($q) use ($channels) {
                    foreach ($channels as $key => $channel) {
                        $q->orWhere('products.channel', 'like', '%' . $channel . '%');
                    }
                });

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
                'products.sku', 'products.source_url',
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
                    $query->where('products.sku', request('sku'));
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
            if (!$regions) {
                return api_response(['message' => 'Mã tỉnh thành không tồn tại'], 404);
            }

            $channels = request('channels', [2]);

            $product = Product::with('manufacturer', 'category')
                ->select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`,  `products`.`description`, `products`.`image` as `source_url`, `products`.`manufacturer_id`, `products`.`category_id`, `product_supplier`.`quantity`, `product_supplier`.`import_price`"))
                ->leftJoin('product_supplier', function ($q) use ($regions) {
                    $q->on('product_supplier.product_id', '=', 'products.id')
                        ->where('product_supplier.region_id', $regions[0])
                        ->where('product_supplier.state', '=', 1);
                })
                ->where(function ($q) use ($channels) {
                    foreach ($channels as $key => $channel) {
                        $q->orWhere('products.channel', 'like', '%' . $channel . '%');
                    }
                })
                ->findOrFail($id); // kiểm tra thông tin sản phẩm cần mua

            $margin = MarginRegionCategory::where('category_id', $product->category_id)
                ->whereIn('region_id', $regions)->first(); // tính margin của category sản phẩm

            $marginValue = ($margin ? 1 + 0.01 * $margin->margin : 1.05); // giá trị của margin

            $province = Province::whereIn('code', request('province_ids'))->pluck('id'); // tìm tỉnh mua hàng

            $provinceFee = TransportFee::where('province_id', $province ? $province[0] : 0)->first();// phí ship của tỉnh mua hàng

            $feeValue = ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01; //giá trị của phí ship của tỉnh mua hàng

            $provinceIds = Province::where('region_id', $regions[0])->pluck('id');
            $provinceFeeMax = TransportFee::whereIn('province_id', $provinceIds)->orderBy('percent_fee', 'DESC')->first();

            $minPrice = DB::select("SELECT
                                    product_supplier.*, supplier_supported_province.province_id, supplier_supported_province.province_name, transport_fees.percent_fee
                                FROM
                                    `product_supplier`
                                LEFT JOIN `supplier_supported_province` ON `product_supplier`.`supplier_id` = `supplier_supported_province`.`supplier_id`
                                LEFT JOIN `transport_fees` ON `transport_fees`.`province_id` = `supplier_supported_province`.`province_id`
                                WHERE
                                    `product_id` = ?
                                AND `product_supplier`.`region_id` = ?
                                AND `product_supplier`.`state` = 1
                                ORDER BY
                                    (
                                        IF (
                                            product_supplier.price_recommend > 0,
                                            product_supplier.price_recommend,
                                            product_supplier.import_price * (
                                                ? + ? + (
                                                    CASE
                                                    WHEN supplier_supported_province.province_id = ? THEN
                                                        0
                                                    ELSE
                                                    IF (
                                                        transport_fees.percent_fee IS NULL,
                                                        0,
                                                        transport_fees.percent_fee / 100
                                                    )
                                                    END
                                                )
                                            )
                                        )
                                    ) ASC,
                                    `transport_fees`.`percent_fee` ASC
                                LIMIT 1
                     ", [$id, $regions[0], $marginValue, $feeValue, $province[0]]);
            // giá tốt nhất tìm được trong miền sau khi cộng margin và fee

            if ($minPrice) {
                $provinceFeeMin = SupplierSupportedProvince::with('transportFee')
                    ->where('supplier_id', $minPrice ? $minPrice[0]->supplier_id : 0)
                    ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'supplier_supported_province.province_id')
                    ->orderBy('transport_fees.percent_fee')
                    ->first(); // lấy phí vận chuyển thấp nhất của ncc cung cấp sản phẩm với giá tốt nhất

                $supportedProvince = SupplierSupportedProvince::where('supplier_id', $minPrice ? $minPrice[0]->supplier_id : 0)->pluck('province_id');
                //kiểm tra nhà cung cấp sản phẩm có hỗ trợ cho tỉnh mua hàng ko

                if (in_array($province[0], $supportedProvince ? $supportedProvince->toArray() : []) || !$provinceFeeMin) {
                    $productMargin = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01;
                } else {
                    $productMargin = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01 + ($provinceFeeMin->transportFee ? $provinceFeeMin->transportFee->percent_fee : 0) * 0.01;
                }
                $productFeeMax = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFeeMax ? $provinceFeeMax->percent_fee : 0) * 0.01 * 2;
                $w_margin = ($margin ? $margin->margin : 5) * 0.01;

                $product->best_price = ProductSupplier::where('product_id', $id)
                    ->where('product_supplier.region_id', $regions[0])
                    ->where('product_supplier.state', '=', 1)
                    ->where('product_supplier.supplier_id', $minPrice[0]->supplier_id)
                    ->min(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * ' . $productMargin . '/1000) * 1000))'));

                $product->import_price = ProductSupplier::where('product_id', $id)
                    ->where('product_supplier.region_id', $regions[0])
                    ->where('product_supplier.state', '=', 1)
                    ->where('product_supplier.supplier_id', $minPrice[0]->supplier_id)
                    ->min(DB::raw('ceil(product_supplier.import_price * (' . $productMargin . '-' . $w_margin . ')/1000) * 1000'));

                $product->import_price_w_margin = ProductSupplier::where('product_id', $id)
                    ->where('product_supplier.region_id', $regions[0])
                    ->where('product_supplier.state', '=', 1)
                    ->where('product_supplier.supplier_id', $minPrice[0]->supplier_id)
                    ->min(DB::raw('ceil(product_supplier.import_price * ' . $productMargin . '/1000) * 1000'));

                $product->recommended_price = ProductSupplier::where('product_id', $id)
                    ->where('product_supplier.region_id', $regions[0])
                    ->where('product_supplier.state', '=', 1)
                    ->where('price_recommend', $product->best_price)
                    ->where('product_supplier.supplier_id', $minPrice[0]->supplier_id)
                    ->min('product_supplier.price_recommend');

                $product->official_price = $product->recommended_price ? : ceil(rtrim(rtrim(sprintf('%f', $minPrice[0]->import_price * $productFeeMax / 1000), '0'), '.')) * 1000;

                if ($product->recommended_price == $product->best_price) {
                    $suppliers = ProductSupplier::where('price_recommend', $product->best_price)
                        ->where('product_id', $id)
                        ->leftJoin('suppliers', 'product_supplier.supplier_id', '=', 'suppliers.id')
                        ->where('product_supplier.region_id', $regions[0])
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

                $product->status = true;

                return $product;
            } else {
                $product->status = false;
                $product->message = 'Sản phẩm không tồn tại hoặc không có nhà cung cấp';
                return $product;
            }

        } catch (\Exception $e) {
            return api_response(['message' => $e->getMessage()], 500);
        }
    }

    public function show(Product $product)
    {
        $product->product_options = config('teko.stores', []);
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

        $product = Product::forceCreate([
            'category_id' => $category->id,
            'manufacturer_id' => $manufacturer->id,
            'color_id' => $color ? $color->id : 0,
            'name' => $productData['name'],
            'code' => $productData['code'],
            'status' => 0,
        ]);

        event(new ProductUpserted($product));

        return $product;
    }

    public function updateNameBySku()
    {
        $results = [];
        foreach (request('form_data') as $productData) {
            try {
                $product = Product::where('sku', $productData['sku'])->first();

                if ($product) {
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

        $query = 'select p.id,p.sku,p.name,c.name as cname,m.name as mname,ps.supplier_name,ps.import_price,
                         ps.price_recommend,ps.updated_at, case ps.region_id
                           when 1 then "Miền Bắc"
                           when 2 then "Miền Trung"
                           when 3 then "Miền Nam"
                           else "N/A"
                         end as region_name
                    from products as p
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
