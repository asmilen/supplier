<?php

namespace App\Http\Controllers\API;

use Validator;
use App\Models\Province;
use App\Http\Controllers\Controller;

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

        return Province::getTransportFeesList();
    }
}
