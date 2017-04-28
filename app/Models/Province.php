<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    public static function getActiveList()
    {
        return static::pluck('name', 'id')->all();
    }

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}

