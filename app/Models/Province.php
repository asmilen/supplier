<?php

namespace App\Models;

use DB;
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

    public static function getTransportFeesList()
    {
        return DB::table('provinces')
            ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'provinces.id')
            ->whereIn('provinces.code', request('province_codes'))
            ->select(DB::raw('provinces.code as province_code, provinces.name as province_name, ifnull(transport_fees.percent_fee, 0) as shipping_fee'))
            ->get()
            ->keyBy('province_code');
    }
}
