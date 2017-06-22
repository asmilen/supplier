<?php

namespace App\Models;

use DB;
use Datatables;
use App\Jobs\PublishMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $casts = [
        'category_id' => 'string',
        'manufacturer_id' => 'string',
        'color_id' => 'string',
        'parent_id' => 'string',
        'status' => 'boolean',
    ];

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
            ->editColumn('status', 'products.datatables.status')
            ->addColumn('action', 'products.datatables.action')
            ->rawColumns(['status', 'action'])
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
}