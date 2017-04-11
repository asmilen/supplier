<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSupplier extends Model
{
    //
    protected $table = 'product_supplier';

    protected $fillable = [
        'code',
        'import_price',
        'vat',
        'status',
        'state',
    ];


    public static $STATE_HET_HANG = 0;
    public static $STATE_CON_HANG = 1;
    public static $STATE_DAT_HANG = 2;

    public static $STATUS_CHO_DUYET = 0;
    public static $STATUS_CAP_NHAT = 1;
    public static $STATUS_DA_DANG = 2;
    public static $STATUS_YEU_CAU_DANG = 3;


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
