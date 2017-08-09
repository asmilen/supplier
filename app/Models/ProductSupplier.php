<?php

namespace App\Models;

use DB;
use Sentinel;
use Datatables;
use App\Models\SupplierProductLog;
use Illuminate\Database\Eloquent\Model;

class ProductSupplier extends Model
{
    use Trackable, HasUpdater;

    protected $table = 'product_supplier';

    protected $fillable = [
        'product_id',
        'supplier_id',
        'code',
        'import_price',
        'price_recommend',
        'vat',
        'status',
        'state',
        'from_date',
        'to_date',
        'description',
        'image',
        'name',
        'quantity',
        'created_by',
        'updated_by',
    ];


    public static $STATE_HET_HANG = 0;
    public static $STATE_CON_HANG = 1;
    public static $STATE_DAT_HANG = 2;

    public static $STATUS_CHO_DUYET = 0;
    public static $STATUS_HET_HANG = 1;
    public static $STATUS_UU_TIEN_LAY_HANG = 2;
    public static $STATUS_YEU_CAU_UU_TIEN_LAY_HANG = 3;
    public static $STATUS_KHONG_UU_TIEN_LAY_HANG = 4;



    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public static function boot()
    {
        parent::boot();

        static::updating(function ($productsupplier) {
            /**
             * @var ProductSupplier $productsupplier
             */
            SupplierProductLog::forceCreate([
                'product_id' => $productsupplier->product_id,
                'supplier_id' => $productsupplier->supplier_id,
                'current_data' => json_encode($productsupplier->getOriginal()),
                'update_data' => json_encode($productsupplier),
                'created_by' => Sentinel::getUser()->id,
            ]);
        });
    }

    public static function getListByRegion($regionId)
    {
        return collect(DB::select('
                SELECT ps.product_id, p.sku, ps.import_price, ps.state, ps.created_at, ps.updated_at FROM product_supplier AS ps
                INNER JOIN products AS p ON ps.product_id = p.id
                WHERE supplier_id IN (
                    SELECT supplier_id FROM supplier_supported_province
                    WHERE province_id IN (
                        SELECT id FROM provinces WHERE region_id = '.$regionId.'
                    )
                )
                ORDER BY updated_at DESC, created_at DESC
        '))->groupBy('product_id');
    }

    public function scopeCanManage($query)
    {
        $user = Sentinel::getUser();

        if (! $user->isSuperAdmin()) {
            return $query;
        }

        $supplierIds = collect(DB::select('select supplier_id from supplier_supported_province where province_id in (select id from provinces where region_id in (select region_id from user_supported_province where supported_id = ?))', [$user->id]))
            ->pluck('supplier_id')
            ->toArray();

        return $query->whereIn('supplier_id', $supplierIds);
    }

    public static function getDatatables()
    {
        $model = static::select('*')->with('product', 'product.category', 'product.manufacturer', 'supplier', 'creater', 'updater')
            ->canManage();

        return Datatables::eloquent($model)
            ->filter(function ($query) {
                if (request()->has('category_id')) {
                    $query->whereHas('product', function ($query) {
                        $query->where('category_id', request('category_id'));
                    });
                }

                if (request()->has('manufacturer_id')) {
                    $query->whereHas('product', function ($query) {
                        $query->where('manufacturer_id', request('manufacturer_id'));
                    });
                }

                if (request()->has('supplier_id')) {
                    $query->where('supplier_id', request('supplier_id'));
                }

                if (request()->has('keyword')) {
                    $query->whereHas('product', function ($query) {
                        $query->where('name', 'like', '%'.request('keyword').'%')
                            ->orWhere('sku', 'like', '%'.request('keyword').'%');
                    });
                }

                if (request()->has('state')) {
                    $query->where('state', request('state'));
                }
            })
            ->addColumn('category_name', function ($productSupplier) {
                return isset($productSupplier->product->category->name) ? $productSupplier->product->category->name : '';
            })
            ->addColumn('manufacturer_name', function ($productSupplier) {
                return isset($productSupplier->product->manufacturer->name) ? $productSupplier->product->manufacturer->name : '';
            })
            ->addColumn('sku', function ($productSupplier) {
                return $productSupplier->product->sku ?: '';
            })
            ->addColumn('product_name', function ($productSupplier) {
                return $productSupplier->product->name ?: '';
            })
            ->addColumn('supplier_name', function ($productSupplier) {
                return $productSupplier->supplier->name ?: '';
            })
            ->editColumn('import_price', function ($productSupplier) {
                return number_format(round($productSupplier->import_price, 0));
            })
            ->editColumn('state', function ($productSupplier) {
                return config('teko.product.state')[$productSupplier->state] ? : 'N/A';
            })
            ->editColumn('updated_by', function ($productSupplier) {
                if (isset($productSupplier->updater->name)) {
                    return $productSupplier->updater->name;
                }

                if (isset($productSupplier->creater->name)) {
                    return $productSupplier->creater->name;
                }

                return '';
            })
            ->make(true);
    }
}
