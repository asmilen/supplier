<?php

namespace App\Http\Controllers;


use DB;
use Auth;
use Sentinel;
use Validator;
use Datatables;
use App\Models\Product;
use App\Models\Suppliers;
use Illuminate\Http\Request;
use App\Models\ProductSupplier;
use App\Models\UserSupportedProvince;
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
    public function mapping(Request $request)
    {
        $rules = [
            'supplier_id' => 'required',
            'state' => 'required',
            'import_price' => 'required|integer|min:0',
            'vat' => 'required|integer|min:0',
            'price_recommend' => 'required|integer|min:0',
            'image' => 'required|mimes:jpeg,bmp,png|image|max:1024',
            'description' => 'required',
            'quantity' => 'required|integer|min:0',
        ];
        $messages = [
            'supplier_id.required' => 'Hãy chọn nhà cung cấp',
            'state.required' => 'Hãy chọn tình trạng sản phẩm',
            'import_price.required' => 'Hãy nhập giá nhập',
            'import_price.integer' => 'Hãy nhập đúng định dạng',
            'import_price.min' => 'Hãy nhập đúng định dạng',
            'vat.required' => 'Hãy nhập VAT',
            'vat.integer' => 'Hãy nhập đúng định dạng',
            'vat.min' => 'Hãy nhập đúng định dạng',
            'price_recommend.required' => 'Hãy nhập giá khuyến nghị',
            'price_recommend.integer' => 'Hãy nhập đúng định dạng',
            'price_recommend.min' => 'Hãy nhập đúng định dạng',
            'image.required' => 'Hãy chọn ảnh sản phẩm ',
            'image.mimes' => 'Hãy chọn 1 tệp có định dạng ảnh',
            'image.max' => 'Hãy chọn 1 tệp có định dạng ảnh không quá 1Mb',
            'description.required' => 'Hãy nhập mô tả',
            'quantity.required' => 'Hãy nhập so luong',
            'quantity.integer' => 'Hãy nhập đúng định dạng',
            'quantity.min' => 'Hãy nhập đúng định dạng',
        ];

        $validator = Validator::make($request->all(), $rules,$messages);

        if($validator->fails()) {
            $errors = $validator->errors();
            $response['status']  = 'fails';
            $response['errors'] = $errors;
        } else {
            $file = request()->file('image');
            $filename = md5(uniqid().'_'.time()) . '.' . $file->getClientOriginalExtension();

            $data = [
                'product_id' => $request->input('product_id'),
                'supplier_id' => $request->input('supplier_id'),
                'import_price' => $request->input('import_price'),
                'vat' => $request->input('vat'),
                'price_recommend' => $request->input('price_recommend'),
                'image' => $filename,
                'state' => $request->input('state'),
                'quantity' => $request->input('quantity'),
                'description' => $request->input('description')

            ];

            $product = Product::find($data['product_id']);
            $codes_supplier = Suppliers::where('id',$data['supplier_id'])->select('code')->first();
            $data['name'] = $product->name;
            $data['code'] = $codes_supplier->code;
            $data['created_by'] = Sentinel::getUser()->id;
            $data['updated_by'] = Sentinel::getUser()->id;

            $product_supplier = ProductSupplier::firstOrCreate($data);
            if($product_supplier) {
                Image::make($file->getRealPath())->save(storage_path('app/public/' . $filename));
                $response['status']  = 'success';
            } else {
                $response['status']  = 'exists';
            }
        }
        return json_encode($response);

    }

    public function getDatatables() {

        $user_id = Sentinel::getUser()->id;
        $products = UserSupportedProvince::join('provinces', 'user_supported_province.region_id', '=', 'provinces.region_id')
            ->join('supplier_supported_province', 'provinces.id', '=', 'supplier_supported_province.province_id')
            ->join('product_supplier', 'supplier_supported_province.supplier_id', '=', 'product_supplier.supplier_id')
            ->join('products', 'product_supplier.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('manufacturers', 'products.manufacturer_id', '=', 'manufacturers.id')
            ->where('user_supported_province.supported_id',$user_id)
            ->where('user_supported_province.status',1)
            ->whereIn('product_supplier.state', [1, 2])
            ->whereIn('product_supplier.status', [0, 2])
            ->groupBy('product_supplier.product_id')
            ->select(DB::raw('product_supplier.id as id,product_supplier.product_id as id_product,categories.name as cat_name, products.sku as sku,
                    products.name as product_name,product_supplier.import_price as import_price, product_supplier.vat,product_supplier.status as status,
                    product_supplier.price_recommend as recommend_price, manufacturers.name as manufacturer_name,
                    product_supplier.updated_at as updated_at,product_supplier.state as status_product,provinces.region as region, min(import_price)'))->get();
        return Datatables::of($products)
            ->editColumn('import_price', function($product) {
                return number_format($product->import_price);
            })
            ->editColumn('vat', function($product) {
                return number_format($product->vat);
            })
            ->editColumn('saler_price', function($product) {
                $saler_price = number_format($product->import_price + $product->vat);
                return $saler_price;
            })
            ->editColumn('recommend_price', function($product) {
                return number_format($product->recommend_price);
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
                } else if($product->status_product == 1){
                    $string = 'Còn hàng';
                } else if($product->status_product == 2){
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

        $user_id = Sentinel::getUser()->id;
        $products = UserSupportedProvince::join('provinces', 'user_supported_province.region_id', '=', 'provinces.region_id')
            ->join('supplier_supported_province', 'provinces.id', '=', 'supplier_supported_province.province_id')
            ->join('product_supplier', 'supplier_supported_province.supplier_id', '=', 'product_supplier.supplier_id')
            ->join('suppliers', 'product_supplier.supplier_id', '=', 'suppliers.id')
            ->where('user_supported_province.supported_id',$user_id)
            ->where('product_supplier.product_id',$id)
            ->select('product_supplier.id as id','product_supplier.image as image','product_supplier.description as description', 'product_supplier.name as product_name',
                'product_supplier.import_price as import_price', 'product_supplier.vat','product_supplier.status as status','product_supplier.state as state',
                'suppliers.name as supplier_name','suppliers.id as supplier_id','product_supplier.price_recommend as recommend_price')->get();
        $supplier_product_arr = [];
        foreach ($products as $value) {
            array_push($supplier_product_arr,$value->supplier_id);
        }
        $suppliers_arr = UserSupportedProvince::join('provinces', 'user_supported_province.region_id', '=', 'provinces.region_id')
            ->join('supplier_supported_province', 'provinces.id', '=', 'supplier_supported_province.province_id')
            ->where('user_supported_province.supported_id',$user_id)
            ->select('supplier_supported_province.supplier_id as supplier_id')
            ->get();
        $supplier_arr = [];
        foreach ($suppliers_arr as $value) {
            array_push($supplier_arr,$value->supplier_id);
        }
        $supplier_id_arr = array_merge(array_diff($supplier_product_arr, $supplier_arr), array_diff($supplier_arr, $supplier_product_arr));
        $suppliers = DB::table('suppliers')->whereIn('id', $supplier_id_arr)->where('status',1)->get();

        return view('suppliers.products_suppliers',compact('products','id','suppliers'));
    }

}