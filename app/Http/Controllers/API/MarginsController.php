<?php

namespace App\Http\Controllers\API;

use App\Models\Margin;
use App\Http\Controllers\Controller;

class MarginsController extends Controller
{
    public function index()
    {
        return Margin::all('label','value');
    }
}


