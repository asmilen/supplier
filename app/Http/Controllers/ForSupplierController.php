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
use File;


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

        request()->merge([
            'code' => htmlentities(request('code'))
        ]);

        $this->validate(request(), [
            'product_name' => 'required',
            //'product_id' => 'required',
            'import_price' => 'required|numeric|min:0',
            'vat' => 'required|numeric|min:0',
            'code' => 'required',
            'state' => 'required|in:' . implode(',', array_keys(config('teko.product.state'))),
        ]);

        if (request('product_supplier_id'))
        {
            $supplier_product = ProductSupplier::find(request('product_supplier_id'));
        }
        else {
            $supplier_product = ProductSupplier::where('product_id', '=', request('product_id'))
                ->where('supplier_id', '=', $supplier_id)
                ->first();
        }

        if ($supplier_product) {
            $current = $supplier_product->toJson();

            if (request()->file('image') && request()->file('image')->isValid()) {
                $image = $this->saveImage(request()->file('image'), $supplier_product->image);
            }

            $supplier_product->forceFill([
                'code' => request('code'),
                'import_price' => request('import_price'),
                'description' => request('description'),
                'vat' => request('vat'),
                'state' => request('state'),
                'status' => ProductSupplier::$STATUS_CHO_DUYET,
                'image' => isset($image) ? $image:"",
                'updated_by' => $user_id,
            ])->save();;
            $updated = $supplier_product->toJson();

            // update log
            DB::table('supplier_product_logs')->insert([
                'product_id' => $supplier_product->product_id,
                'supplier_id' => $supplier_product->supplier_id,
                'current_data' => $current,
                'update_data'  => $updated,
                'created_by'    => $user_id,
                'created_at' =>  \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }
        else
        {
            if (request()->file('image') && request()->file('image')->isValid()) {
                $image = $this->saveImage($request->file('image'), null);
            }

            $supplier_product = ProductSupplier::forceCreate([
                'product_id' => request('product_id') ? request('product_id'):0,
                'supplier_id' => $supplier_id,
                'name' => request('product_name'),
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
                'image' => isset($image) ? $image:"",
            ]);

            // update log
            DB::table('supplier_product_logs')->insert([
                'product_id' => $supplier_product->product_id,
                'supplier_id' => $supplier_product->supplier_id,
                'current_data' => $supplier_product->toJson(),
                'update_data'  => $supplier_product->toJson(),
                'created_by'    => $user_id,
                'created_at' =>  \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }

        //tìm giá sản phẩm là thấp nhất
        /*if ($product) {
            $affected = ProductSupplier::where('product_id', '=', $product->id)->where('state', '<>', ProductSupplier::$STATE_HET_HANG)
                ->whereIn('status', [ProductSupplier::$STATUS_KHONG_UU_TIEN_LAY_HANG, ProductSupplier::$STATUS_UU_TIEN_LAY_HANG, ProductSupplier::$STATUS_YEU_CAU_UU_TIEN_LAY_HANG])
                ->update(['status' => ProductSupplier::$STATUS_KHONG_UU_TIEN_LAY_HANG]);

            $best_price = ProductSupplier::where('product_id', '=', $product->id)->where('state', '<>', ProductSupplier::$STATE_HET_HANG)
                ->whereIn('status', [ProductSupplier::$STATUS_KHONG_UU_TIEN_LAY_HANG, ProductSupplier::$STATUS_UU_TIEN_LAY_HANG, ProductSupplier::$STATUS_YEU_CAU_UU_TIEN_LAY_HANG])
                ->min('import_price');

            $best_price_product = ProductSupplier::where('product_id', '=', $product->id)
                ->where('state', '<>', ProductSupplier::$STATE_HET_HANG)
                ->whereIn('status', [ProductSupplier::$STATUS_KHONG_UU_TIEN_LAY_HANG, ProductSupplier::$STATUS_UU_TIEN_LAY_HANG, ProductSupplier::$STATUS_YEU_CAU_UU_TIEN_LAY_HANG])
                ->where('import_price', '=', $best_price)
                ->orderBy('state')
                ->first();
            if ($best_price_product) {
                if ($best_price_product->state == ProductSupplier::$STATE_CON_HANG)
                    $best_price_product->status = ProductSupplier::$STATUS_UU_TIEN_LAY_HANG;
                else
                    $best_price_product->status = ProductSupplier::$STATUS_YEU_CAU_UU_TIEN_LAY_HANG;
                $best_price_product->save();
            }
        }*/
        flash()->success('Success!', 'Cập nhật giá thành công');
        return redirect()->route('supplier.updatePrice');
    }

    public function saveImage($file, $old = null)
    {
        $cdate = date('dmY');
        $path = storage_path('app/' . $cdate);
        if(!File::exists($path)) File::makeDirectory($path);
        $filename = md5(uniqid().'_'.time()) . '.' . $file->getClientOriginalExtension();
        Image::make($file->getRealPath())->save($path . '/' . $filename);
        if ($old) {
            @unlink(storage_path('app/' . $old));
        }
        return $cdate. '/' . $filename;
    }

    public function ajaxGetProductById()
    {
        if (request()->has('product_id'))
        {
            $user_id = Sentinel::getUser()->id;
            $supplier_account = DB::table('supplier_accounts')->where('user_id','=',$user_id)->first();
            $supplier_id = $supplier_account->supplier_id;

            $product_supplier = ProductSupplier::where('product_id' ,'=',request('product_id'))
                                                ->where('supplier_id','=',$supplier_id)->first();
            if ($product_supplier)
            {
                return response()->json([
                    'status' => true,
                    'data' => [
                        'product_name' => $product_supplier->name,
                        'import_price' => $product_supplier->import_price,
                        'vat' => $product_supplier->vat,
                        'state' => $product_supplier->state,
                        'code' => $product_supplier->code,
                        'description' => $product_supplier->description,
                    ],
                ]);
            }
        }
        return response()->json([
            'status' => false,
        ]);
    }

    public function ajaxGetProductByName()
    {
        if (request()->has('product_name'))
        {
            $products = Product::where('name', 'like', '%'.request('product_name').'%')
                ->where('status',true)
                ->select('id','name','sku')
                ->limit(5)
                ->get();

            if ($products)
            {
                return response()->json([
                    'status' => true,
                    'data' => $products,
                ]);
            }
        }
        return response()->json([
            'status' => false,
        ]);
    }

    public function getDatatables(Request $request)
    {
        $user_id = Sentinel::getUser()->id;
        $supplier_account = DB::table('supplier_accounts')->where('user_id','=',$user_id)->first();
        $supplier_id = $supplier_account ? $supplier_account->supplier_id : null;

        $query_builder = DB::table('product_supplier')
                        ->leftJoin('products','product_supplier.product_id','=','products.id')
                        ->leftJoin('categories','products.category_id','=','categories.id')
                        ->where('supplier_id','=',$supplier_id)
                        ->select('product_supplier.id as product_supplier_id','products.id as id','products.category_id','categories.name as category_name','product_supplier.description','product_supplier.product_id',
                            'product_supplier.name as product_name','product_supplier.updated_at','product_supplier.status','state','vat','product_supplier.code' , DB::raw('FLOOR(import_price) as import_price'));
        return Datatables::queryBuilder($query_builder)
            ->filter(function ($query) {
                if (request()->has('product_name')) {
                    $query->where('product_supplier.name', 'like', '%'.request('product_name').'%');
                }
                if (request()->has('category_name')) {
                    $query->where('categories.name', 'like', '%'.request('category_name').'%');
                }
                if (request()->has('import_price')) {
                    $query->where('import_price', 'like', '%'.request('import_price').'%');
                }
                if (request()->has('status')) {
                    $query->where('product_supplier.status', 'like', '%'.request('status').'%');
                }
            })
            ->editColumn('status', 'suppliers.datatables.status')
            ->editColumn('import_price', 'suppliers.datatables.import_price')
            ->rawColumns(['status','import_price'])
            ->make(true);
    }
}
