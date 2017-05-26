<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class TransportFee extends Model
{
    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public static function getList()
    {
        return DB::table('provinces')
            ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'provinces.id')
            ->select(DB::raw('provinces.id as province_id, provinces.name as province_name, ifnull(transport_fees.percent_fee, 0) as percent_fee'))
            ->get();
    }
}
