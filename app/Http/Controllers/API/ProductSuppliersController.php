<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Http\Controllers\Controller;

class ProductSuppliersController extends Controller
{
    public function updatePriceFromGoolgeSheet()
    {
        $results = [];

        foreach (request()->all() as $data) {
            array_push($results, [Carbon::now(), 'Đã cập nhật.']);
        }

        return response()->json($results);
    }
}
