<?php

namespace App\Models;

use phpDocumentor\Reflection\Types\Array_;
use Sentinel;
use DB;
use Datatables;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Jobs\PublishMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Product extends Model
{
    use SoftDeletes, Trackable, HasUpdater;

    protected $dates = ['deleted_at'];

    protected $casts = [
        'category_id' => 'string',
        'manufacturer_id' => 'string',
        'color_id' => 'string',
        'parent_id' => 'string',
        'status' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $code = $model->code ?: $model->id;

            $model->forceFill([
                'code' => $code,
                'sku' => $model->generateSku($code),
            ])->save();
        });

        static::saved(function ($model) {
            if ($model->sku) {
                dispatch(new PublishMessage('teko.sale', 'sale.product.upsert', json_encode([
                    'id' => $model->id,
                    'categoryId' => $model->category_id,
                    'brandId' => $model->manufacturer_id,
                    'type' => $model->type,
                    'sku' => $model->sku,
                    'name' => $model->name,
                    'skuIdentifier' => $model->code,
                    'status' => $model->status ? 'active' : 'inactive',
                    'sourceUrl' => $model->source_url,
                    'createdAt' => strtotime($model->created_at),
                ])));
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function saleprices()
    {
        return $this->hasMany(Saleprice::class)->orderBy('updated_at', 'desc');
    }

    public function children()
    {
        return $this->hasMany(Product::class, 'parent_id');
    }

    public function productSuppliers()
    {
        return $this->hasMany(ProductSupplier::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public static function getDatatables()
    {
        $model = static::select([
            'id', 'category_id', 'manufacturer_id', 'name', 'code', 'image', 'sku', 'status',
        ])->with('category', 'manufacturer');

        return Datatables::eloquent($model)
            ->filter(function ($query) {
                if (request()->has('keyword')) {
                    $query->where(function ($query) {
                        $query->where('name', 'like', '%' . request('keyword') . '%')
                            ->orWhere('code', 'like', '%' . request('keyword') . '%')
                            ->orWhere('sku', 'like', '%' . request('keyword') . '%');
                    });
                }

                if (request()->has('category_id')) {
                    $query->where('category_id', request('category_id'));
                }

                if (request()->has('manufacturer_id')) {
                    $query->where('manufacturer_id', request('manufacturer_id'));
                }

                if (request('status') == 'active') {
                    $query->where('status', true);
                } elseif (request('status') == 'inactive') {
                    $query->where('status', false);
                }

                if (request()->has('type')) {
                    $query->where('type', request('type'));
                }
            })
            ->editColumn('category_id', function ($model) {
                return $model->category ? $model->category->name : '';
            })
            ->editColumn('manufacturer_id', function ($model) {
                return $model->manufacturer ? $model->manufacturer->name : '';
            })
            ->editColumn('image', 'products.datatables.image')
            ->editColumn('status', 'products.datatables.status')
            ->addColumn('action', 'products.datatables.action')
            ->rawColumns(['image', 'status', 'action'])
            ->make(true);
    }

    public function addSaleprice($data)
    {
        if (!isset(config('teko.stores')[$data['store_id']])) {
            throw new \Exception('Store không tồn tại.');
        }

        if (!isset(config('teko.regions')[$data['region_id']])) {
            throw new \Exception('Miền không tồn tại.');
        }

        $saleprice = (new Saleprice)->forceFill($data);

        $this->saleprices()->save($saleprice);

        dispatch(new PublishMessage('teko.sale', 'sale.price.update', json_encode([
            'storeId' => $saleprice->store_id,
            'storeName' => config('teko.stores')[$saleprice->store_id],
            'regionId' => $saleprice->region_id,
            'regionName' => config('teko.regions')[$saleprice->region_id],
            'productId' => $this->id,
            'sku' => $this->sku,
            'price' => $saleprice->price,
            'createdAt' => time(),
        ])));

        return $this;
    }

    public static function getProductInCombo($productIds)
    {
        $model = static::select([
            'id', 'name', 'code', 'source_url', 'sku', 'status',
        ])->where('products.type', 0)->whereNotIn('products.id', $productIds);

        return Datatables::of($model)
            ->editColumn('status', 'products.datatables.status')
            ->addColumn('check', function ($product) {
                return '<input  type="checkbox" value="' . $product->id . '" class="checkbox"/>';
            })
            ->addColumn('quantity', function () {
                return '<input  class="qty"  type="number" min = 0 value="1"/>';
            })
            ->rawColumns(['status', 'check', 'quantity'])
            ->make(true);
    }

    public static function getSimpleProduct()
    {
        $model = static::select([
            'id', 'name', 'code', 'source_url', 'sku', 'status',
        ])->where('products.type', 0);

        return Datatables::of($model)
            ->editColumn('status', 'products.datatables.status')
            ->addColumn('add', function ($product) {
                return '<a href="#"><i class="ace-icon fa fa-plus" aria-hidden="true"></i></a>';
            })
            ->rawColumns(['status', 'add'])
            ->make(true);
    }

    public function scopeHasNoSuppliers($query)
    {
        return $query->where('products.status', true)
            ->leftJoin('product_supplier', 'products.id', '=', 'product_supplier.product_id')
            ->whereNull('product_supplier.id');
    }

    public function scopeHasImportPriceExpired($query)
    {
        return $query->where('products.status', true)
            ->join('product_supplier', 'products.id', '=', 'product_supplier.product_id')
            ->whereNotNull('product_supplier.to_date')
            ->where('product_supplier.to_date', '<=', Carbon::now());
    }

    public function scopeHasImportPriceExpiredSoon($query, $days = 2)
    {
        return $query->where('products.status', true)
            ->join('product_supplier', 'products.id', '=', 'product_supplier.product_id')
            ->whereNotNull('product_supplier.to_date')
            ->where('product_supplier.to_date', '>', Carbon::now())
            ->where('product_supplier.to_date', '<=', Carbon::now()->addDays($days));
    }

    protected function generateSku($code)
    {
        $sku = $this->category->code . '-' . $this->manufacturer->code . '-' . $code;

        if ($this->color) {
            $sku .= '-' . $this->color->code;
        }

        return $sku;
    }

    public function updatePriceToMagento()
    {
        $supplier_ids = ProductSupplier::where('product_id', $this->id)
            ->pluck('id');
        for ($i = 0; $i < $supplier_ids->count(); $i++) {
            ProductSupplier::where('supplier_id', '!=', 0)
                ->where('id', $supplier_ids[$i])
                ->where('state', 1)
                ->chunk(10, function ($products) {
                    foreach ($products as $product) {

                        $region = ProductSupplier::where('product_supplier.id', $product->id)
                            ->leftJoin('supplier_supported_province', 'supplier_supported_province.supplier_id', '=', 'product_supplier.supplier_id')
                            ->leftJoin('provinces', 'provinces.id', '=', 'supplier_supported_province.province_id')
                            ->first();

                        $detail = $region ? $region->region_id : 0;

                        $province_id = Province::where('region_id', $region->region_id)->pluck('id');
                        $provinceFee = TransportFee::whereIn('province_id', $province_id)->orderBy('percent_fee', 'DESC')->first();
                        /**
                         * @var array $supplierIds
                         */
                        $supplierIds = SupplierSupportedProvince::whereIn('province_id', $province_id)
                            ->get()
                            ->pluck('supplier_id');

                        $product = Product::with('manufacturer', 'category')
                            ->select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`, `products`.`image` as `source_url`, `products`.`manufacturer_id`, `products`.`category_id`, `product_supplier`.`quantity`"))
                            ->leftJoin('product_supplier', function ($q) use ($supplierIds) {
                                $q->on('product_supplier.product_id', '=', 'products.id')
                                    ->whereIn('product_supplier.supplier_id', $supplierIds)
                                    ->where('product_supplier.state', '=', 1);
                            })
                            ->findOrFail($product->product_id);
                        if ($product) {
                            $margin = MarginRegionCategory::where('category_id', $product->category_id)
                                ->where('region_id', $region->region_id)->first();

                            $importPrice = ProductSupplier::where('product_id', $product->id)
                                ->whereIn('product_supplier.supplier_id', $supplierIds)
                                ->where('product_supplier.state', '=', 1)
                                ->orderBy('product_supplier.import_price')
                                ->first();

                            $import_price = ceil(($importPrice ? $importPrice->import_price : 0) * (1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01 * 2) / 1000) * 1000;
                            $Price = ProductSupplier::where('product_id', $product->id)
                                ->whereIn('product_supplier.supplier_id', $supplierIds)
                                ->where('product_supplier.state', '=', 1)
                                ->select(DB::raw(" * , case when `product_supplier`.`price_recommend` > 0
                        and `product_supplier`.`price_recommend` <  $import_price then `product_supplier`.`price_recommend` else $import_price end as min_price"))
                                ->orderBy('min_price')
                                ->first();
                            $PriceRecommend = ProductSupplier::where('product_id', $product->id)
                                ->whereIn('product_supplier.supplier_id', $supplierIds)
                                ->where('product_supplier.state', '=', 1)
                                ->select(DB::raw(" * , case when `product_supplier`.`price_recommend` > 0
                        then `product_supplier`.`price_recommend` else 1000000000 end as min_price"))
                                ->orderBy('min_price')
                                ->first();

                            $count = ProductSupplier::where('product_id', $product->id)
                                ->whereIn('product_supplier.supplier_id', $supplierIds)
                                ->where('product_supplier.state', '=', 1)
                                ->where('product_supplier.price_recommend', 0)->count();

                            if ($count === 0) {
                                $minPrice = $PriceRecommend;
                            } else {
                                $minPrice = $Price;
                            }

                            if ($minPrice) {
                                $post_data = [
                                    'data' => [
                                        [
                                            'region_id' => $region ? $region->region_id : 0,
                                            'sku' => $product ? $product->sku : 0,
                                            'price' => $minPrice ? $minPrice->min_price : 0
                                        ]
                                    ]
                                ];

                                $log = PostPriceToMgtLog::where('product_id', $product->id)
                                    ->orderBy('created_at', 'DESC')
                                    ->first();

                                if ($log) {
                                    if ($log->product_id == $product->id && json_encode($post_data) == $log->post_data) {
                                        if ($detail != $log->detail) {
                                            $response = $this->callApi($post_data);
                                            PostPriceToMgtLog::create([
//                                                'user_id' => Sentinel::getUser()->id,
                                                'product_id' => $product->id,
                                                'detail' => $region ? $region->region_id : 0,
                                                'post_data' => json_encode($post_data),
                                                'response' => json_encode($response)
                                            ]);
                                        }
                                    } else {
                                        $response = $this->callApi($post_data);
                                        PostPriceToMgtLog::create([
//                                            'user_id' => Sentinel::getUser()->id,
                                            'product_id' => $product->id,
                                            'detail' => $region ? $region->region_id : 0,
                                            'post_data' => json_encode($post_data),
                                            'response' => json_encode($response)
                                        ]);
                                    }
                                } else{
                                    $response = $this->callApi($post_data);
                                    PostPriceToMgtLog::create([
//                                        'user_id' => Sentinel::getUser()->id,
                                        'product_id' => $product->id,
                                        'detail' => $region ? $region->region_id : 0,
                                        'post_data' => json_encode($post_data),
                                        'response' => json_encode($response)
                                    ]);
                                }

                            }
                        }
                    }
                });
        }
    }

    private function callApi($data)
    {
        $client = new Client(['base_uri' => env('UPDATE_PRICE_URL_BASE'), 'verify' => false]);
        /**
         * @var \GuzzleHttp\Psr7\Response $result
         */
        $result = $client->post(env('UPDATE_PRICE_TO_MAGENTO_URL'), [
            'body' => json_encode($data),
        ]);

        if (null === $decodedResult = json_decode($result->getBody()->getContents(), true)) {
            return array('errorMessage' => 'Could not decode json');
        }
        return $decodedResult;
    }
}
