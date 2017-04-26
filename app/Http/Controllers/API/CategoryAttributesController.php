<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use App\Http\Controllers\Controller;

class CategoryAttributesController extends Controller
{
    public function index(Category $category)
    {
        return $category->attributes;
    }
}
