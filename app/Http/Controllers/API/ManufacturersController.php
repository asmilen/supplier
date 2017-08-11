<?php

namespace App\Http\Controllers\API;

use App\Models\Manufacturer;
use App\Http\Controllers\Controller;

class ManufacturersController extends Controller
{
    public function index()
    {
        return Manufacturer::active()->get();
    }
}
