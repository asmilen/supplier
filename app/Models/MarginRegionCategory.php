<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarginRegionCategory extends Model
{
    protected $table = 'margin_region_category';

    public $timestamps = false;

    protected $fillable = ['category_id', 'region_id', 'margin'];
}
