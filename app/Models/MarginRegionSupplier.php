<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MarginRegionSupplier
 * @package App\Models
 * @property integer supplier_id
 * @property integer region_id
 * @property integer margin
 * @method integer getSupplier()
 * @method integer getRegionId()
 * @method integer getMargin()
 */
class MarginRegionSupplier extends Model
{
    protected $table = 'margin_region_supplier';

    protected $fillable = ['supplier_id', 'region_id', 'margin'];
}
