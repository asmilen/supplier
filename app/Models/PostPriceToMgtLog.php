<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostPriceToMgtLog extends Model
{

    protected $table = 'post_price_to_mgt_logs';
    protected $fillable = [
        'region_id',
        'product_id',
        'detail',
        'post_data',
        'response',
        'created_at',
        'updated_at'
    ];
}
