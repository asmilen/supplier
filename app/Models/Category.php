<?php

namespace App\Models;

use Datatables;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\UpdateCategoryPriceToMagento;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes, Trackable, HasUpdater;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class);
    }

    public function margins()
    {
        return $this->hasMany(MarginRegionCategory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public static function getActiveList()
    {
        return static::active()->pluck('name', 'id')->all();
    }

    public function updatePriceToMagento()
    {
        dispatch(new UpdateCategoryPriceToMagento($this));
    }
}
