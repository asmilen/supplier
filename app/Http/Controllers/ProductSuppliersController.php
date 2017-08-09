<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\Manufacturer;
use App\Models\ProductSupplier;

class ProductSuppliersController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        $manufacturers = Manufacturer::all();

        $suppliers = Supplier::all();

        return view('product-suppliers.index', compact('categories', 'manufacturers', 'suppliers'));
    }

    public function getDatatables()
    {
        return ProductSupplier::getDatatables();
    }
}
