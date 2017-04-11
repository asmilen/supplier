<?php

namespace App\Http\Controllers\API;

use App\Models\Supplier;
use App\Http\Controllers\Controller;

class SuppliersController extends Controller
{
    public function index()
    {
        return ['data' => Supplier::all(['id', 'name', 'code'])];
    }
}
