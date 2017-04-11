<?php

namespace App\Http\Controllers;


use DB;
use Auth;
use Validator;
use Datatables;
use App\Models\Product;
use App\Models\Suppliers;
use Illuminate\Http\Request;
use App\Models\ProductSupplier;
use Intervention\Image\Facades\Image as Image;

class SuppliersController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('suppliers.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        Validator::make($request->all(), [
            'supplier_id' => 'required',
            'state' => 'required',
            'import_price' => 'required',
            'vat' => 'required',
            'saler_price' => 'required',
            'image' => 'required|mimes:jpeg,bmp,png|image|max:1024',
            'description' => 'required',
        ])->validate();

        $file = request()->file('image');
        $filename = md5(time()) . '.' . $file->getClientOriginalExtension();
        $data = [
            'product_id' => $request->input('product_id'),
            'supplier_id' => $request->input('supplier_id'),
            'import_price' => $request->input('import_price'),
            'vat' => $request->input('vat'),
            'image' => $filename,
            'state' => $request->input('state'),
            'quantity' => $request->input('quantity'),
            'description' => $request->input('description')

        ];

        $product = Product::find($data['product_id']);
        $codes_supplier = Suppliers::where('id',$data['supplier_id'])->select('code')->first();
        $data['name'] = $product->name;
        $data['code'] = $codes_supplier->code;
        $data['created_id'] = $request->user()->id;
        $data['updated_id'] = $request->user()->id;
        $product_supplier = ProductSupplier::firstOrCreate($data);
        if($product_supplier) {
            Image::make($file->getRealPath())->save(public_path('files/'. $filename));
            flash()->success('Success!', 'Suppliers successfully created.');
        } else {
            flash()->success('Failed!', 'Suppliers failed created.');
        }

        return redirect()->back();
    }

    public function getDatatables() {

        $products = DB::table('suppliers')
            ->join('product_supplier', 'suppliers.id', '=', 'product_supplier.supplier_id')
            ->join('products', 'product_supplier.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('manufacturers', 'products.manufacturer_id', '=', 'manufacturers.id')
            ->select('product_supplier.id as id','product_supplier.product_id as id_product','categories.name as cat_name', 'products.sku as sku', 'products.name as product_name','product_supplier.import_price as import_price',
                     'product_supplier.vat','product_supplier.status as status','suppliers.name as supplier_name', 'manufacturers.name as manufacturer_name',
                    'product_supplier.updated_at as updated_at','product_supplier.state as status_product')->get();

        return Datatables::of($products)
            ->editColumn('saler_price', function($product) {
                return $product->import_price + $product->vat;
            })
            ->editColumn('tekshop_price', function($product) {
                return $product->import_price;
            })
            ->editColumn('status', function($product) {
                if($product->status == 0){
                    $string = 'Chờ duyệt';
                } else if($product->status == 1){
                    $string = 'Câp nhật';
                } else if($product->status == 2){
                    $string = 'Đã đăng';
                } else if($product->status == 3){
                    $string = 'Yêu cầu đăng';
                }
                return $string;
            })->editColumn('status_product', function($product) {
                if($product->status_product == 0){
                    $string = 'Hết hàng';
                } else if($product->status == 1){
                    $string = 'Còn hàng';
                } else if($product->status == 2){
                    $string = 'Đặt hàng';
                }
                return $string;
            })
            ->addColumn('action',function($product){
                $url = url('suppliers/show/'.$product->id_product);
                $string = '<a  href="'.$url.'" class="btn btn-outline btn-circle red btn-sm purple"><i class="fa fa-edit"></i></a>';
                return $string;
            })->make(true);
    }

    public function updateDatatables(Request $request) {

        $data_arr = $request->input('data');

        foreach ($data_arr as $key => $value) {
            if($value['status'] == 'Chờ duyệt') {
                $res = DB::table('product_supplier')->where('id', $key)->update(['status' => '0']);
            } else if($value['status'] == 'Câp nhật'){
                $res = DB::table('product_supplier')->where('id', $key)->update(['status' => '1']);
            } else if($value['status'] == 'Đã đăng'){
                $res = DB::table('product_supplier')->where('id', $key)->update(['status' => '2']);
            } else if($value['status'] == 'Yêu cầu đăng'){
                $res = DB::table('product_supplier')->where('id', $key)->update(['status' => '3']);
            }
        }
    }

    protected function show($id) {

        $products = DB::table('suppliers')
            ->join('product_supplier', 'suppliers.id', '=', 'product_supplier.supplier_id')
            ->join('products', 'product_supplier.product_id', '=', 'products.id')
            ->where('product_supplier.product_id',$id)
            ->select('product_supplier.id as id','product_supplier.product_id as id_product', 'products.sku as sku', 'product_supplier.name as product_name','product_supplier.import_price as import_price',
                'product_supplier.vat','product_supplier.status as status','suppliers.name as supplier_name',
                'product_supplier.updated_at as updated_at','products.status as status_product')->get();
        $suppliers = DB::table('suppliers')->get();

        return view('suppliers.products_suppliers',compact('products','id','suppliers'));
    }

}
