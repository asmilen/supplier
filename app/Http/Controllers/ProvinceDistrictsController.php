<?php

namespace App\Http\Controllers;

use App\Models\Province;

class ProvinceDistrictsController extends Controller
{
    public function index(Province $province)
    {
        return $province->districts;
    }
}
