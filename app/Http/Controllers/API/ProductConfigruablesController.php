<?php

namespace App\Http\Controllers\API;

use DB;
use Validator;
use Datatables;
use App\Models\Product;
use App\Http\Controllers\Controller;

class ProductConfigruablesController extends Controller
{
    public function index()
    {
        return Product::with('children')->where('type', 1)->get();
    }
}
