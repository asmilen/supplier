<?php

namespace App\Http\Controllers;

use App\Models\Province;

class ProvincesController extends Controller
{
    public function index()
    {
        return Province::all();
    }

    public function getAddressCode(Province $province)
    {
        return $province->code;
    }
}
