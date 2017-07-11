<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalepriceLog extends Model
{

    protected $table = 'saleprice_logs';
    protected $fillable = [
        'user_id',
        'sku',
        'price',
        'store',
        'region',
        'status',
        'created_at',
        'updated_at',
    ];
    public $timestamps = true;
}
