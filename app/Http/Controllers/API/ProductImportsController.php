<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class ProductImportsController extends Controller
{
    public function importFromGoogleSheet()
    {
        \Log::info(request()->all());
    }
}
