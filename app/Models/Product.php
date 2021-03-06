<?php

namespace App\Models;

use DB;
use Image;
use Sentinel;
use Datatables;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Jobs\PublishMessage;
use App\Jobs\OffProductToMagento;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Array_;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    protected $with = ['category', 'manufacturer', 'color'];

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
        return $this->hasMany(ProductSupplier::class)->where('state', 1);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
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

        dispatch(new PublishMessage('teko.sale', 'sale.price.update', [
            'storeId' => $saleprice->store_id,
            'storeName' => config('teko.stores')[$saleprice->store_id],
            'regionId' => $saleprice->region_id,
            'regionName' => config('teko.regions')[$saleprice->region_id],
            'productId' => $this->id,
            'sku' => $this->sku,
            'price' => $saleprice->price,
            'createdAt' => time(),
        ]));

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

    public function updatePriceToMagento($regionId)
    {
        $supplier_ids = ProductSupplier::where('product_id', $this->id)
            ->leftJoin('products', 'product_supplier.product_id', 'products.id')
            ->where('product_supplier.state', 1)
            ->pluck('product_supplier.id');

        for ($i = 0; $i < $supplier_ids->count(); $i++) {
            ProductSupplier::where('supplier_id', '!=', 0)
                ->where('id', $supplier_ids[$i])
                ->where('state', 1)
                ->where('region_id', $regionId)
                ->chunk(200, function ($productSupplier) use ($regionId) {
                    $province_id = Province::where('region_id', $regionId)->pluck('id');
                    $provinceFee = TransportFee::whereIn('province_id', $province_id)->orderBy('percent_fee', 'DESC')->first();
                    /**
                     * @var array $supplierIds
                     */

                    $product = Product::with('manufacturer', 'category')
                        ->select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`, `products`.`image` as `source_url`, `products`.`manufacturer_id`, `products`.`category_id`, `product_supplier`.`quantity`"))
                        ->leftJoin('product_supplier', function ($q) use ($regionId) {
                            $q->on('product_supplier.product_id', '=', 'products.id')
                                ->where('product_supplier.region_id', $regionId)
                                ->where('product_supplier.state', '=', 1);
                        })
                        ->findOrFail($this->id);

                    if ($product) {
                        $margin = MarginRegionCategory::where('category_id', $product->category_id)
                            ->where('region_id', $regionId)->first();

                        $importPrice = ProductSupplier::where('product_id', $product->id)
                            ->where('product_supplier.region_id', $regionId)
                            ->where('product_supplier.state', '=', 1)
                            ->where('product_supplier.price_recommend', '=', 0)
                            ->orderBy('product_supplier.import_price')
                            ->first();

                        $import_price = ceil(intval(($importPrice ? $importPrice->import_price : 0) * (1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01 * 2)) / 1000) * 1000;

                        $Price = ProductSupplier::where('product_id', $product->id)
                            ->where('product_supplier.region_id', $regionId)
                            ->where('product_supplier.state', '=', 1)
                            ->select(DB::raw(" * , case when `product_supplier`.`price_recommend` > 0
                        and `product_supplier`.`price_recommend` <  $import_price then `product_supplier`.`price_recommend` else $import_price end as min_price"))
                            ->orderBy('min_price')
                            ->first();
                        'if((if(product_supplier.price_recommend > 0, product_supplier.price_recommend, 10000000000)) = 10000000000, 0 ,
								(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, 10000000000))) as recommended_price';
                        $PriceRecommend = ProductSupplier::where('product_id', $product->id)
                            ->where('product_supplier.region_id', $regionId)
                            ->where('product_supplier.state', '=', 1)
                            ->select(DB::raw(" * , case when `product_supplier`.`price_recommend` > 0
                        then `product_supplier`.`price_recommend` else 1000000000 end as min_price"))
                            ->orderBy('min_price')
                            ->first();
                        $count = ProductSupplier::where('product_id', $product->id)
                            ->where('product_supplier.region_id', $regionId)
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
                                        'region_id' => $regionId ? $regionId : 0,
                                        'sku' => $product ? $product->sku : 0,
                                        'price' => $minPrice ? $minPrice->min_price : 0
                                    ]
                                ]
                            ];

                            $log = PostPriceToMgtLog::where('product_id', $product->id)
                                ->where('region_id', $regionId ? $regionId : 0)
                                ->orderBy('created_at', 'DESC')
                                ->first();

                            if ($log) {
                                if (json_encode($post_data) != $log->post_data) {

                                    $product_supplier_ids = ProductSupplier::where('product_id', $product->id)
                                        ->pluck('id');
                                    $detail = [];
                                    for ($i = 0; $i < $product_supplier_ids->count(); $i++) {
                                        $productValue = ProductSupplier::where('product_supplier.id', $product_supplier_ids[$i])
                                            ->leftJoin('supplier_supported_province', 'product_supplier.supplier_id', 'supplier_supported_province.supplier_id')
                                            ->where('product_supplier.region_id', $regionId)
                                            ->select(DB::raw('product_supplier.*'))
                                            ->first();
                                        if ($productValue) {
                                            $detail = array_merge([[
                                                'product' => $productValue,
                                                'margin' => ($margin ? $margin->margin : 5) * 0.01,
                                                'fee' => ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01 * 2
                                            ]], is_array($detail) ? $detail : []);
                                        }
                                    }

                                    $response = $this->callApi($post_data);
                                    PostPriceToMgtLog::create([
                                        'region_id' => $regionId ? $regionId : 0,
                                        'product_id' => $product->id,
                                        'detail' => json_encode($detail),
                                        'post_data' => json_encode($post_data),
                                        'response' => json_encode($response)
                                    ]);
                                }
                            } else {
                                $product_supplier_ids = ProductSupplier::where('product_id', $product->id)
                                    ->pluck('id');
                                $detail = [];
                                for ($i = 0; $i < $product_supplier_ids->count(); $i++) {
                                    $productValue = ProductSupplier::where('product_supplier.id', $product_supplier_ids[$i])
                                        ->leftJoin('supplier_supported_province', 'product_supplier.supplier_id', 'supplier_supported_province.supplier_id')
                                        ->where('product_supplier.region_id', $regionId)
                                        ->select(DB::raw('product_supplier.*'))
                                        ->first();
                                    if ($productValue) {
                                        $detail = array_merge([[
                                            'product' => $productValue,
                                            'margin' => ($margin ? $margin->margin : 5) * 0.01,
                                            'fee' => ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01 * 2
                                        ]], is_array($detail) ? $detail : []);
                                    }
                                }

                                $response = $this->callApi($post_data);
                                PostPriceToMgtLog::create([
                                    'region_id' => $regionId ? $regionId : 0,
                                    'product_id' => $product->id,
                                    'detail' => json_encode($detail),
                                    'post_data' => json_encode($post_data),
                                    'response' => json_encode($response)
                                ]);
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

    //off product to magento
    public function offProductToMagento($regionId)
    {
        $post_data = [
            'data' => [
                'status' => 0,
                'regionId' => $regionId,
                'products' => $this->sku
            ]
        ];

        $response = $this->callApiOffProduct($post_data);

        LogOffProduct::create([
            'product_id' => $this ? $this->id : 0,
            'type' => 'OFF',
            'post_data' => json_encode($post_data),
            'response' => json_encode($response)
        ]);
    }

    public function onProductToMagento($regionId)
    {
        $post_data = [
            'data' => [
                'status' => 1,
                'regionId' => $regionId,
                'products' => $this->sku
            ]
        ];

        $response = $this->callApiOffProduct($post_data);

        LogOffProduct::create([
            'product_id' => $this ? $this->id : 0,
            'type' => 'ON',
            'post_data' => json_encode($post_data),
            'response' => json_encode($response)
        ]);
    }

    private function callApiOffProduct($data)
    {
        $client = new Client(['base_uri' => env('OFF_PRODUCT_URL_BASE'), 'verify' => false]);
        /**
         * @var \GuzzleHttp\Psr7\Response $result
         */
        $result = $client->post(env('OFF_PRODUCT_URL'), [
            'body' => json_encode($data),
        ]);

        if (null === $decodedResult = json_decode($result->getBody()->getContents(), true)) {
            return array('errorMessage' => 'Could not decode json');
        }
        return $decodedResult;
    }

    public function updateAttributes($values)
    {
        foreach ($this->category->attributes()->get() as $attribute) {
            $value = isset($values[$attribute->slug]) ? $values[$attribute->slug] : '';

            $this->updateAttribute($attribute, $value);
        }

        array_walk_recursive($values, function (&$item, $key) {
            $item = null === $item ? '' : $item;
        });

        $this->forceFill([
            'attributes' => json_encode($values),
        ])->save();

        return $this;
    }

    public function updateAttribute($attribute, $value)
    {
        $className = $this->getProductAttributeClassName($attribute->backend_type);

        if (is_array($value)) {
            foreach ($value as $v) {
                $className::updateOrCreate([
                    'product_id' => $this->id,
                    'attribute_id' => $attribute->id,
                    'value' => $v,
                ]);
            }

            $className::where('product_id', $this->id)
                ->where('attribute_id', $attribute->id)
                ->whereNotIn('value', $value)
                ->delete();
        } else {
            return $className::updateOrCreate([
                'product_id' => $this->id,
                'attribute_id' => $attribute->id,
            ], [
                'value' => $value,
            ]);
        }
    }

    protected function getProductAttributeClassName($type)
    {
        return 'App\Models\ProductAttribute'.ucfirst($type);
    }

    public function generateSku($code = null, $force = false)
    {
        if (! empty($this->sku) && ! $force) {
            return $this;
        }

        $code = $code ? : $this->id;

        $sku = $this->category->code . '-' . $this->manufacturer->code . '-' . $code;

        if ($this->color) {
            $sku .= '-' . $this->color->code;
        }

        $this->forceFill([
            'code' => $code,
            'sku' => $sku,
        ])->save();

        return $this;
    }

    public function setChannels($channels)
    {
        $this->forceFill([
            'channel' => implode(',', $channels),
        ])->save();

        return $this;
    }

    public function isDisabled()
    {
        return ! $this->status;
    }

    public function isAvailableToOfflineStore()
    {
        return in_array(2, explode(',', $this->channel));
    }

    public function isNotAvailableToOfflineStore()
    {
        return ! $this->isAvailableToOfflineStore();
    }

    public function needToOffOnMagento()
    {
        return $this->isDisabled() || $this->isNotAvailableToOfflineStore();
    }

    public function setOffOnMagento()
    {
        $regionIds = ProductSupplier::where('product_id', $this->id)->distinct()->get(['region_id'])->pluck('region_id');

        foreach ($regionIds as $regionId) {
            dispatch(new OffProductToMagento($this, 0, Sentinel::getUser(), $regionId));
        }
    }

    public function broadcastUpserted()
    {
        dispatch(new PublishMessage('teko.sale', 'sale.product.upsert', [
            'id' => $this->id,
            'categoryId' => $this->category_id,
            'brandId' => $this->manufacturer_id,
            'type' => $this->type,
            'sku' => $this->sku,
            'name' => $this->name,
            'skuIdentifier' => $this->code,
            'status' => $this->status ? 'active' : 'inactive',
            'sourceUrl' => $this->source_url,
            'createdAt' => strtotime($this->created_at),
        ]));
    }

    public function setFeaturedImage($imageBase64)
    {
        if (empty($imageBase64)) {
            return false;
        }

        $img = Image::make($imageBase64['base64']);

        $filename = md5(uniqid() . '_' . time()) . '_' . $imageBase64['filename'];

        $img->save(storage_path('app/public/' . $filename));

        $this->forceFill([
            'image' => url('/storage/' . $filename),
        ])->save();

        return $this;
    }
}
