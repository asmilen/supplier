<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{

    protected $table = 'provinces';

    public static function getActiveList()
    {
        return static::pluck('name', 'id')->all();
    }
}
