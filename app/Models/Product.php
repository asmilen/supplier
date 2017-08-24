<?php

namespace App\Models;

use DB;
use Datatables;
use Carbon\Carbon;
use App\Jobs\PublishMessage;
use Illuminate\Database\Eloquent\Model;
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

        static::saved(function ($model)
        {
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
        return $this->hasMany(Saleprice::class)->orderBy('updated_at','desc');
    }

    public function children()
    {
        return $this->hasMany(Product::class, 'parent_id');
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
                        $query->where('name', 'like', '%'.request('keyword').'%')
                            ->orWhere('code', 'like', '%'.request('keyword').'%')
                            ->orWhere('sku', 'like', '%'.request('keyword').'%');
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
        if (! isset(config('teko.stores')[$data['store_id']])) {
            throw new \Exception('Store không tồn tại.');
        }

        if (! isset(config('teko.regions')[$data['region_id']])) {
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
        ])->where('products.type',0)->whereNotIn('products.id',$productIds);

        return Datatables::of($model)
            ->editColumn('status', 'products.datatables.status')
            ->addColumn('check', function ($product) {
                return '<input  type="checkbox" value="' . $product->id . '" class="checkbox"/>';
            })
            ->addColumn('quantity', function () {
                return '<input  class="qty"  type="number" min = 0 value="1"/>';
            })
            ->rawColumns(['status','check', 'quantity'])
            ->make(true);
    }

    public static function getSimpleProduct()
    {
        $model = static::select([
            'id', 'name', 'code', 'source_url', 'sku', 'status',
        ])->where('products.type',0);

        return Datatables::of($model)
            ->editColumn('status', 'products.datatables.status')
            ->addColumn('add', function ($product) {
                return '<a href="#"><i class="ace-icon fa fa-plus" aria-hidden="true"></i></a>';
            })
            ->rawColumns(['status','add'])
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
        $sku = $this->category->code.'-'.$this->manufacturer->code.'-'.$code;

        if ($this->color) {
            $sku .= '-'.$this->color->code;
        }

        return $sku;
    }

    public function updatePriceToMagento()
    {
        //
    }
}
