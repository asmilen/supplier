<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'provinces';

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public static function getActiveList()
    {
        return static::pluck('name', 'id')->all();
    }
}