<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public static function getList()
    {
        return static::pluck('name', 'id')->all();
    }
}
