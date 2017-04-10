<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Models\Product;
use App\Models\Category;
use App\Models\Manufacturer;
use Datatables;


class ForSupplierController extends Controller
{
//    public function __construct()
//    {
//        view()->share('categoriesList', Category::getList());
//        view()->share('manufacturersList', Manufacturer::getList());
//    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function updatePrice()
    {

        return view('suppliers.update_price');
    }

    public function getDatatables()
    {
        $query_builder = DB::table('product_supplier')
                        ->join('products','product_supplier.product_id','=','products.id')
                        ->join('categories','products.category_id','=','categories.id')
                        ->select('product_supplier.id as id','products.category_id','categories.name as category_name','products.name as product_name','import_price','product_supplier.updated_at','product_supplier.status');
        return Datatables::queryBuilder($query_builder)
            ->filter(function ($query) {
                if (request()->has('keyword')) {
                    $query->where('name', 'like', '%'.request('keyword').'%');
                }

                if (request()->has('category_id')) {
                    $query->where('category_id', request('category_id'));
                }

                if (request('status') == 'active') {
                    $query->where('status', true);
                } elseif (request('status') == 'inactive') {
                    $query->where('status', false);
                }
            })
            ->make(true);
    }
}
