<?php

namespace App\Http\Controllers;

use App\Models\ProductSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Models\Product;
use App\Models\Category;
use App\Models\Manufacturer;
use Datatables;
use Sentinel;


class ForSupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function updatePrice(Request $request)
    {
        $supplier_id = 1;
        if ($request->isMethod('post')) {
            //
            $this->validate(request(), [
                'product_name' => 'required',
                'product_id' => 'required',
                'import_price' => 'required',
                'vat' => 'required',
            ]);

            $data = $request->all();
            $supplier_product = ProductSupplier::where('product_id','=',request('category_id'))
                                                ->where('supplier_id','=',$supplier_id)
                                                ->first();
            if ($supplier_product)
            {
                $supplier_product->update($request);
            }
            else
            {
                $product = Product::find(request('product_id'));
                $supplier_product = ProductSupplier::forceCreate([
                    'product_id' => request('product_id'),
                    'supplier_id' => $supplier_id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'import_price' => request('import_price'),
                    'vat' => request('import_price'),
                    'state' => request('state'),
                    'quantity' => 1,
                    'extra_condition' => "",
                    'created_by' => Sentinel::getUser()->id,
                    'updated_by' => Sentinel::getUser()->id,
                ]);
            }

            flash()->success('Success!', 'Cập nhật giá thành công');
        }
        return view('suppliers.update_price');
    }

    public function getDatatables()
    {
        $supplier_id = 1;
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
