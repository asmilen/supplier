<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Attribute;

class CategoryUnassignedAttributesController extends Controller
{
    public function index(Category $category)
    {
        return Attribute::whereNotIn(
                'id', $category->attributes()->pluck('id')
            )->orderBy('slug', 'asc')->get();
    }
}
