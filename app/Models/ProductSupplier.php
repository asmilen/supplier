<?php

namespace App\Models;

use DB;
use App\Models\SupplierProductLog;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

class ProductSupplier extends BaseModel
{
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
}
