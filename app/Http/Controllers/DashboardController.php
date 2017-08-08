<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Manufacturer;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $countCategories = Category::active()->count();
        $countManufacturers = Manufacturer::active()->count();
        $countSuppliers = Supplier::where('status', true)->count();
        $countProducts = Product::where('status', true)->count();

        $countProductsHasNoSuppliers = Product::hasNoSuppliers()->count();
        $countSuppliersHasNoProducts = Supplier::hasNoProducts()->count();
        $countProductsHasImportPriceExpired = Product::hasImportPriceExpired()->count();
        $countProductsHasImportPriceExpiredSoon = Product::hasImportPriceExpiredSoon(7)->count();

        return view('dashboard', compact(
            'countCategories',
            'countManufacturers',
            'countSuppliers',
            'countProducts',
            'countProductsHasNoSuppliers',
            'countSuppliersHasNoProducts',
            'countProductsHasImportPriceExpired',
            'countProductsHasImportPriceExpiredSoon'
        ));
    }
}
