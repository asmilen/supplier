<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogOffProduct extends Model
{

    protected $table = 'log_off_products';
    protected $fillable = [
        'product_id',
        'type',
        'post_data',
        'response',
        'created_at',
        'updated_at'
    ];
}
