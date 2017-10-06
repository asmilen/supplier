<?php

namespace App\Models;

use App\Jobs\PublishMessage;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\UpdateCategoryPriceToMagento;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes, Trackable, HasUpdater;

    protected $dates = ['deleted_at'];

    protected $casts = [
        'status' => 'boolean',
    ];

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

    public function broadcastUpserted()
    {
        $message = json_encode([
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'status' => $this->status ? 'active' : 'inactive',
            'createdAt' => strtotime($this->updated_at),
        ]);

        dispatch(new PublishMessage('teko.sale', 'sale.cat.upsert', $message));
    }
}
