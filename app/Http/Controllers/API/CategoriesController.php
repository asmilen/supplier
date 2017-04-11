<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use App\Http\Controllers\Controller;

class CategoriesController extends Controller
{
    public function index()
    {
        return Category::all(['id', 'name', 'code']);
    }
}
