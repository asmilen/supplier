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
use Image;


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
        $product = Product::find(request('product_id'));

        if ($supplier_product)
        {
            $data = $request->all();
            $data['updated_by'] = $user_id;
            $data['status'] = ProductSupplier::$STATUS_CAP_NHAT;

            //Nếu giá sản phẩm là thấp nhất
            if ($data['state'] != ProductSupplier::$STATE_HET_HANG && $product->best_price && $data['import_price'] < $product->best_price )
            {
                $product->best_price = $data['import_price'];
                $product->save();
                if ($data['state'] == ProductSupplier::$STATE_CON_HANG)
                    $data['status'] = ProductSupplier::$STATUS_DA_DANG;
                else
                    $data['status'] = ProductSupplier::$STATUS_YEU_CAU_DANG;
            }

            if (request()->file('image') && request()->file('image')->isValid()) {
                $data['image'] = $this->saveImage(request()->file('image'), $supplier_product->image);
            } else {
                unset($data['image']);
            }

            $supplier_product->update($data);
        }
        else
        {
            if (request()->file('image') && request()->file('image')->isValid()) {
                $image = $this->saveImage($request->file('image'), null);
            }

            $supplier_product = ProductSupplier::forceCreate([
                'product_id' => request('product_id'),
                'supplier_id' => $supplier_id,
                'name' => $product->name,
                'code' => request('code'),
                'import_price' => request('import_price'),
                'description' => request('description'),
                'vat' => request('vat'),
                'state' => request('state'),
                'quantity' => 0,
                'status'   => ProductSupplier::$STATUS_CHO_DUYET,
                'extra_condition' => "",
                'created_by' => $user_id,
                'updated_by' => $user_id,
                'image' => $image,
            ]);
        }

        flash()->success('Success!', 'Cập nhật giá thành công');
        return redirect()->route('supplier.updatePrice');
    }

    public function saveImage($file, $old = null)
    {
        $filename = md5(uniqid().'_'.time()) . '.' . $file->getClientOriginalExtension();
        Image::make($file->getRealPath())->save(public_path('files/' . $filename));
        if ($old) {
            @unlink(public_path('files/' . $old));
        }
        return $filename;
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
                        ->select('products.id as id','products.category_id','categories.name as category_name','product_supplier.description',
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
