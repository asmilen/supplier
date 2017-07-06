<?php

namespace App\Http\Controllers\API;

use App\Models\Province;
use App\Models\TransportFee;
use App\Http\Controllers\Controller;
use Validator;
use DB;

class TransportFeesController extends Controller
{
    public function index()
    {
        $validator = Validator::make(request()->all(), [
            'province_codes' => 'array',
            'province_codes.*' => 'required',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        return DB::table('provinces')
            ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'provinces.id')
            ->whereIn('provinces.code', request('province_codes'))
            ->select(DB::raw('provinces.code as province_code, provinces.name as province_name, ifnull(transport_fees.percent_fee, 0) as shipping_fee'))
            ->get()
            ->keyBy('province_code');
    }
}
