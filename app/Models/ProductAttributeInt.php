<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeInt extends Model
{
    protected $fillable = ['product_id', 'attribute_id', 'value'];
}
