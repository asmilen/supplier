<?php

namespace App\Http\Controllers\API;

use App\Models\Color;
use App\Http\Controllers\Controller;

class ColorsController extends Controller
{
    public function index()
    {
        return Color::all(['id', 'name']);
    }
}
