<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeText extends Model
{
    protected $fillable = ['product_id', 'attribute_id', 'value'];
}
