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
        return view('suppliers.update_price');
    }

    protected  function checkLowestPrice($product_id,$price)
    {
        $product_suppliers = ProductSupplier::where('product_id','=',$product_id)->get();
        foreach ($product_suppliers as $product_supplier)
        {
            if ($product_supplier->import_price < $price) return false;
        }
        return true;
    }

    public function postUpdatePrice(Request $request)
    {
        $user_id = Sentinel::getUser()->id;
        $supplier_account = DB::table('supplier_accounts')->where('user_id','=',$user_id)->first();
        $supplier_id = $supplier_account->supplier_id;
            //
        $this->validate(request(), [
            'product_name' => 'required',
            'product_id' => 'required',
            'import_price' => 'required',
            'vat' => 'required',
        ]);

        $data = $request->all();
        $supplier_product = ProductSupplier::where('product_id','=',request('product_id'))
            ->where('supplier_id','=',$supplier_id)
            ->first();

        if ($supplier_product)
        {
            $data = $request->all();
            $data['updated_by'] = $user_id;
            $data['status'] = ProductSupplier::$STATUS_CAP_NHAT;

            //Nếu giá sản phẩm là thấp nhất
            if ($data['state'] != ProductSupplier::$STATE_HET_HANG && self::checkLowestPrice($data['product_id'],$data['import_price']))
            {
                if ($data['state'] == ProductSupplier::$STATE_CON_HANG)
                    $data['status'] = ProductSupplier::$STATUS_DA_DANG;
                else
                    $data['status'] = ProductSupplier::$STATUS_YEU_CAU_DANG;
            }

            $supplier_product->update($data);
        }
        else
        {
            $product = Product::find(request('product_id'));
            $supplier_product = ProductSupplier::forceCreate([
                'product_id' => request('product_id'),
                'supplier_id' => $supplier_id,
                'name' => $product->name,
                'code' => request('code'),
                'import_price' => request('import_price'),
                'vat' => request('vat'),
                'state' => request('state'),
                'quantity' => 0,
                'status'   => ProductSupplier::$STATUS_CHO_DUYET,
                'extra_condition' => "",
                'created_by' => $user_id,
                'updated_by' => $user_id,
            ]);
        }

        flash()->success('Success!', 'Cập nhật giá thành công');
        return redirect()->route('supplier.updatePrice');
    }

    public function getDatatables()
    {
        $user_id = Sentinel::getUser()->id;
        $supplier_account = DB::table('supplier_accounts')->where('user_id','=',$user_id)->first();
        $supplier_id = $supplier_account->supplier_id;

        $query_builder = DB::table('product_supplier')
                        ->join('products','product_supplier.product_id','=','products.id')
                        ->join('categories','products.category_id','=','categories.id')
                        ->where('supplier_id','=',$supplier_id)
                        ->select('products.id as id','products.category_id','categories.name as category_name',
                            'products.name as product_name','import_price','product_supplier.updated_at','product_supplier.status','state','vat','product_supplier.code');
        return Datatables::queryBuilder($query_builder)
            ->filter(function ($query) {
                if (request()->has('keyword')) {
                    $query->where('name', 'like', '%'.request('keyword').'%');
                }
            })
            ->editColumn('status', 'suppliers.datatables.status')
            ->rawColumns(['status'])
            ->make(true);
    }
}
