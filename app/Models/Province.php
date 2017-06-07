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

    public static function getRegionIdsByCode($codeProvince)
    {
        return static::where('code', $codeProvince)->pluck('region_id')->all();
    }

    public static function getListByRegion($regionId)
    {
        return static::where('region_id', $regionId)->pluck('id')->all();
    }
}

