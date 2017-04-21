<?php

namespace App\Http\Controllers;


use DB;
use Auth;
use Carbon;
use Response;
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
        $user_id = Sentinel::getUser()->id;
        $suppliers =  UserSupportedProvince::join('provinces', 'user_supported_province.region_id', '=', 'provinces.region_id')
            ->join('supplier_supported_province', 'provinces.id', '=', 'supplier_supported_province.province_id')
            ->join('suppliers', 'supplier_supported_province.supplier_id', '=', 'suppliers.id')
            ->orderBy('suppliers.name','asc')
            ->where('user_supported_province.supported_id',$user_id)->get();
        $products = Product::all();
        return view('suppliers.index',compact('suppliers','products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function mapping(Request $request)
    {
        $rules = [
            'product_id' => 'required',
            'supplier_id' => 'required',
            'status' => 'required',
            'state' => 'required',
            'import_price' => 'required|integer|min:0',
            'vat' => 'required|integer|min:0',
            'price_recommend' => 'required|integer|min:0',
            'image' => 'required|mimes:jpeg,bmp,png|image|max:1024',
            'description' => 'required',
            'quantity' => 'required|integer|min:0',
        ];
        $messages = [
            'product_id.required' => 'Hãy chọn sản phẩm',
            'supplier_id.required' => 'Hãy chọn nhà cung cấp',
            'status.required' => 'Hãy chọn tình trạng nhà cung cấp',
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
            $product_supplier = ProductSupplier::where('product_id',$request->input('product_id'))->where('supplier_id',$request->input('supplier_id'))->get();
            if(count($product_supplier) > 0) {
                $response['status']  = 'exists';
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
                    'status' => $request->input('status'),
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
                Image::make($file->getRealPath())->save(storage_path('app/public/' . $filename));
                if($data['status'] == 2) {
                    $product->update(['best_price' => $data['import_price']]);
                }

                $response['status']  = 'success';
            }
        }
        return json_encode($response);

    }

    public function getDatatables() {

        $user_id = Sentinel::getUser()->id;
        $products = UserSupportedProvince::join('provinces', 'user_supported_province.region_id', '=', 'provinces.region_id')
            ->join('supplier_supported_province', 'provinces.id', '=', 'supplier_supported_province.province_id')
            ->join('product_supplier', 'supplier_supported_province.supplier_id', '=', 'product_supplier.supplier_id')
            ->join('suppliers', 'product_supplier.supplier_id', '=', 'suppliers.id')
            ->leftJoin('products', 'product_supplier.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('manufacturers', 'products.manufacturer_id', '=', 'manufacturers.id')
            ->where('user_supported_province.supported_id',$user_id)
            ->orderBy('product_supplier.status', 'asc')
            ->select(DB::raw('distinct product_supplier.id as id,product_supplier.product_id as id_product,categories.name as cat_name, products.sku as sku,
                    product_supplier.name as product_name,product_supplier.import_price as import_price, product_supplier.vat,product_supplier.status as status,
                    product_supplier.price_recommend as recommend_price, manufacturers.name as manufacturer_name,
                    product_supplier.updated_at as updated_at,product_supplier.state as status_product,suppliers.name as supplier_name'));

        return Datatables::of($products)
            ->filter(function ($query) {

                if (request()->has('category_name')) {
                    $query->where(function ($query) {
                        $query->where('suppliers.name', 'like', '%'.request('category_name').'%');
                    });
                }

                if (request()->has('manufacture_name')) {
                    $query->where(function ($query) {
                        $query->where('manufacturers.name','like', '%'.request('manufacture_name').'%');
                    });
                }

                if (request()->has('product_sku')) {
                    $query->where('products.sku', request('product_sku'));
                }

                if (request()->has('product_name')) {
                    $query->where('products.name', 'like', '%'.request('product_name').'%');
                }

                if (request()->has('product_import_price')) {
                    $query->where('product_supplier.import_price',request('product_import_price'));
                }

                if (request()->has('vat')) {
                    $query->where('product_supplier.vat',request('vat'));
                }

                if (request()->has('recommend_price')) {
                    $query->where('product_supplier.price_recommend',request('recommend_price'));
                }

                if (request()->has('status')) {
                    $query->where('product_supplier.status', request('status'));
                }

                if (request()->has('supplier_name')) {
                    $query->where('suppliers.name', request('supplier_name'));
                }

                if (request()->has('state')) {
                    $query->where('product_supplier.state', request('state'));
                }

                if (request()->has('updated_at')) {
                    $date = request('updated_at');

                    $from = trim(explode(' - ',$date)[0]);
                    $from = Carbon::createFromFormat('d/m/Y', $from)->startOfDay()->toDateTimeString();

                    $to = trim(explode('-',$date)[1]);
                    $to = Carbon::createFromFormat('d/m/Y', $to)->endOfDay()->toDateTimeString();

                    $query->where('product_supplier.updated_at', '>', $from);
                    $query->where('product_supplier.updated_at', '<',$to);
                }
            })
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
                    $string = 'Hết hàng';
                } else if($product->status == 2){
                    $string = 'Ưu tiên lấy hàng';
                } else if($product->status == 3){
                    $string = 'Yêu cầu ưu tiên lấy hàng';
                } else if($product->status == 4){
                    $string = 'Không ưu tiên lấy hàng';
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
            ->editColumn('updated_at', function($product) {
                return ($product->updated_at) ? date_format($product->updated_at,"Y-m-d") : '';
            })
            ->addColumn('action',function($product){
                $string = '';
                if($product->status == 0) {
                    $string = '<button data-id = "'.$product->id_product.'" class="btn btn-success checkStatus" id = "checkStatus">Duyệt </button>';
                }
                if($product->id_product == 0) {
                    $string .= '<button style = "margin-top:5px" data-id = "'.$product->id.'" class="btn btn-primary connect" id="connect">Liên kết</button>';
                }
                return $string;
            })
            ->make(true);
    }

    protected function getSuppliers(Request $request) {

        $product_id = $request->input('product_id');
        $user_id = Sentinel::getUser()->id;
        $products = UserSupportedProvince::join('provinces', 'user_supported_province.region_id', '=', 'provinces.region_id')
            ->join('supplier_supported_province', 'provinces.id', '=', 'supplier_supported_province.province_id')
            ->join('product_supplier', 'supplier_supported_province.supplier_id', '=', 'product_supplier.supplier_id')
            ->join('suppliers', 'product_supplier.supplier_id', '=', 'suppliers.id')
            ->where('user_supported_province.supported_id',$user_id)
            ->where('product_supplier.product_id',$product_id)
            ->orderBy('product_supplier.status', 'asc')
            ->select(DB::raw('distinct product_supplier.id as id,product_supplier.image as image, product_supplier.name as product_name,product_supplier.product_id as product_id,
                product_supplier.import_price as import_price, product_supplier.vat as vat, product_supplier.status as status, product_supplier.state as state,
                suppliers.name as supplier_name, suppliers.id as supplier_id, product_supplier.price_recommend as recommend_price, product_supplier.updated_at as updated_at'))->get();

        $html = view('suppliers.temp', compact('products'))->render();
        $data = [
            'status' => 'success',
            'data' => $html
        ];
        return Response::json($data);

    }

    public function updateStatus(Request $request) {

        $product_supplier_arr = $request->input('product_supplier_id');
        $product_supplier_status = $request->input('status');
        $products = $request->input('product');
        $best_price = $request->input('best_price');

        for ($i = 0 ;$i< count($product_supplier_arr); $i++) {
            ProductSupplier::find($product_supplier_arr[$i])->update(['status' => $product_supplier_status[$i]]);
            if($product_supplier_status[$i] == 2)
            {
                Product::find($products[$i])->forceFill(['best_price' => $best_price[$i]])->save();
            }
        }

        flash()->success('Success!', 'Status successfully updated.');
        return redirect()->back();
    }

    public function updateIdProduct(Request $request) {

        $product_supplier_id = $request->input('product_supplier_id');
        $product_id = $request->input('product_id');

        ProductSupplier::find($product_supplier_id)->update(['product_id' => $product_id]);

        flash()->success('Success!', 'Status successfully updated.');
        return redirect()->back();
    }

}