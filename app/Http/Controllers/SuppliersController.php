<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Product;
use App\Models\Category;
use App\Models\Manufacturer;
use DB;
use Datatables;
use Intervention\Image\Facades\Image as Image;
class SuppliersController extends Controller
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
    public function index()
    {
        return view('suppliers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $product = new Product;

        return view('products.create', compact('product'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {

        Validator::make(request()->all(), [
            'supplier_id' => 'required|not_in:-- Chọn nhà cung cấp --',
            'state' => 'required|not_in:-- Chọn tình trạng --',
            'import_price' => 'required',
            'vat' => 'required',
            'saler_price' => 'required',
            'image' => 'required|mimes:jpeg,bmp,png|image|max:1024',
            'description' => 'required',
        ])->validate();

        $request = request()->all();
        $file = request()->file('image');

        $filename = md5(time()) . '.' . $file->getClientOriginalExtension();
        Image::make($file->getRealPath())->save(public_path('files/'. $filename));
        dd($filename);
//        if ($old) {
//            @unlink(public_path('files/' .$old));
//        }
//        $product = Product::forceCreate([
//            'category_id' => request('category_id'),
//            'manufacturer_id' => request('manufacturer_id'),
//            'name' => request('name'),
//            'code' => $code,
//            'sku' => $this->generateSku(request('category_id'), request('manufacturer_id'), $code),
//            'status' => !! request('status'),
//        ]);

        flash()->success('Success!', 'Product successfully created.');

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */


    public function getDatatables()
    {

        $products = DB::table('suppliers')
            ->join('product_supplier', 'suppliers.id', '=', 'product_supplier.supplier_id')
            ->join('products', 'product_supplier.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('manufacturers', 'products.manufacturer_id', '=', 'manufacturers.id')
            ->select('product_supplier.id as id','product_supplier.product_id as id_product','categories.name as cat_name', 'products.sku as sku', 'products.name as product_name','product_supplier.import_price as import_price',
                     'product_supplier.vat','product_supplier.status as status','suppliers.name as supplier_name', 'manufacturers.name as manufacturer_name',
                    'product_supplier.updated_at as updated_at','products.status as status_product')->get();

        //dd($products);
        return Datatables::of($products)
            ->editColumn('saler_price', function($product) {
                return $product->import_price + $product->vat;
            })
            ->editColumn('tekshop_price', function($product) {
                return $product->import_price;
            })
            ->editColumn('tekshop_price', function($product) {
                return $product->import_price;
            })
            ->addColumn('action',function($product){
                $url = url('suppliers/product-detail/'.$product->id_product);
                $string = '<a  href="'.$url.'" class="btn btn-outline btn-circle red btn-sm purple"><i class="fa fa-edit"></i></a>';
                return $string;
            })->make(true);
    }

    public function postDatatables(Request $request) {

        $data_arr = $request->input('data');
        $mess = 'false';
        foreach ($data_arr as $key => $value) {
           $res = DB::table('product_supplier')->where('id', $key)->update(['status' => $value['status']]);
           if($res){
               $mess = 'true';
           }
        }

        return response()->json($mess);
    }

    function productDetail($id) {
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

    protected function generateSku($categoryId, $manufacturerId, $code)
    {
        $category = Category::findOrFail($categoryId);
        $manufacturer = Manufacturer::findOrFail($manufacturerId);
        return $category->code.'-'.$manufacturer->code.'-'.$code;
    }
}
