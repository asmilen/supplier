<?php

namespace App\Http\Controllers;


use App\Models\District;
use DB;
use Auth;
use Carbon\Carbon;
use Excel;
use Response;
use Sentinel;
use Validator;
use Datatables;
use App\Models\Product;
use App\Models\Province;
use App\Models\Supplier;
use App\Jobs\PublishMessage;
use Illuminate\Http\Request;
use App\Models\ProductSupplier;
use App\Models\SupplierAddress;
use App\Models\SupplierProductLog;
use App\Models\SupplierBankAccount;
use App\Models\UserSupportedProvince;
use App\Models\SupplierSupportedProvince;
use Intervention\Image\Facades\Image as Image;

class SuppliersController extends Controller
{
    public function __construct()
    {
        view()->share('provincesList', Province::getActiveList());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = Sentinel::getUser()->id;
        $suppliers = UserSupportedProvince::join('provinces', 'user_supported_province.region_id', '=', 'provinces.region_id')
            ->join('supplier_supported_province', 'provinces.id', '=', 'supplier_supported_province.province_id')
            ->join('suppliers', 'supplier_supported_province.supplier_id', '=', 'suppliers.id')
            ->orderBy('suppliers.name', 'asc')
            ->where('user_supported_province.supported_id', $user_id)
            ->select(DB::raw('distinct suppliers.id as supplier_id,suppliers.name as supplier_name,suppliers.code as supplier_code'))
            ->get();
        $products = Product::all();
        return view('suppliers.index', compact('suppliers', 'products'));
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
//            'vat' => 'required|integer|min:0',
//            'price_recommend' => 'required|integer|min:0',
//            'image' => 'required|mimes:jpeg,bmp,png|image|max:1024',
//            'description' => 'required',
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
//            'vat.required' => 'Hãy nhập VAT',
//            'vat.integer' => 'Hãy nhập đúng định dạng',
//            'vat.min' => 'Hãy nhập đúng định dạng',
//            'price_recommend.required' => 'Hãy nhập giá khuyến nghị',
//            'price_recommend.integer' => 'Hãy nhập đúng định dạng',
//            'price_recommend.min' => 'Hãy nhập đúng định dạng',
//            'image.required' => 'Hãy chọn ảnh sản phẩm ',
//            'image.mimes' => 'Hãy chọn 1 tệp có định dạng ảnh',
//            'image.max' => 'Hãy chọn 1 tệp có định dạng ảnh không quá 1Mb',
//            'description.required' => 'Hãy nhập mô tả',
            'quantity.required' => 'Hãy nhập số lượng',
            'quantity.integer' => 'Hãy nhập đúng định dạng',
            'quantity.min' => 'Hãy nhập đúng định dạng',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {

            $errors = $validator->errors();
            $response['status'] = 'fails';
            $response['errors'] = $errors;
        } else {
            $product_supplier = ProductSupplier::where('product_id', $request->input('product_id'))->where('supplier_id', $request->input('supplier_id'))->get();
            if (count($product_supplier) > 0) {
                $response['status'] = 'exists';
            } else {
                $filename = '';
                if(isset($file)){
                    $file = request()->file('image');
                    $filename = md5(uniqid() . '_' . time()) . '.' . $file->getClientOriginalExtension();
                    Image::make($file->getRealPath())->save(storage_path('app/public/' . $filename));
                }

                $data = [
                    'product_id' => $request->input('product_id'),
                    'supplier_id' => $request->input('supplier_id'),
                    'import_price' => $request->input('import_price'),
                    'vat' => $request->input('vat') ? $request->input('vat') : 0,
                    'price_recommend' => $request->input('price_recommend') ? $request->input('price_recommend') : 0,
                    'image' => $filename,
                    'status' => $request->input('status'),
                    'state' => $request->input('state'),
                    'quantity' => $request->input('quantity') ? $request->input('quantity') : 0,
                    'description' => $request->input('description') ? $request->input('description') : ''
                ];

                $product = Product::find($data['product_id']);
                $codes_supplier = Supplier::where('id', $data['supplier_id'])->select('code')->first();
                $data['name'] = $product->name;
                $data['code'] = $codes_supplier->code;
                $data['created_by'] = Sentinel::getUser()->id;
                $data['updated_by'] = Sentinel::getUser()->id;

                $product_supplier = ProductSupplier::firstOrCreate($data);

                if ($data['status'] == 2) {
                    $product->best_price = $data['import_price'];
                    $product->save();
                }

                $response['status'] = 'success';
            }
        }

        return response()->json($response);
    }

    public function getDatatables()
    {
        $user_id = Sentinel::getUser()->id;
        $products = UserSupportedProvince::join('provinces', 'user_supported_province.region_id', '=', 'provinces.region_id')
            ->join('supplier_supported_province', 'provinces.id', '=', 'supplier_supported_province.province_id')
            ->join('product_supplier', 'supplier_supported_province.supplier_id', '=', 'product_supplier.supplier_id')
            ->join('suppliers', 'product_supplier.supplier_id', '=', 'suppliers.id')
            ->leftJoin('products', 'product_supplier.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('manufacturers', 'products.manufacturer_id', '=', 'manufacturers.id')
            ->where('user_supported_province.supported_id', $user_id)
            ->orderBy('product_supplier.updated_at', 'desc')
            ->select(DB::raw('distinct product_supplier.id as id,product_supplier.product_id as id_product,categories.name as cat_name, products.sku as sku,
                    product_supplier.name as product_name,product_supplier.import_price as import_price, product_supplier.vat,product_supplier.status as status,
                    product_supplier.price_recommend as recommend_price, manufacturers.name as manufacturer_name,product_supplier.quantity as supplier_quantity,
                    product_supplier.updated_at as updated_at,product_supplier.state as status_product,suppliers.name as supplier_name'));

        return Datatables::of($products)
            ->filter(function ($query) {

                if (request()->has('category_name')) {
                    $query->where(function ($query) {
                        $query->where('categories.name', 'like', '%' . request('category_name') . '%');
                    });
                }

                if (request()->has('manufacture_name')) {
                    $query->where(function ($query) {
                        $query->where('manufacturers.name', 'like', '%' . request('manufacture_name') . '%');
                    });
                }

                if (request()->has('product_sku')) {
                    $query->where('products.sku', 'like', '%' . request('product_sku') . '%');
                }

                if (request()->has('product_name')) {
                    $query->where('products.name', 'like', '%' . request('product_name') . '%');
                }

                if (request()->has('product_import_price')) {
                    $query->where('product_supplier.import_price', 'like', '%' . request('product_import_price') . '%');
                }
//
//                if (request()->has('vat')) {
//                    $query->where('product_supplier.vat',request('vat'));
//                }

                if (request()->has('recommend_price')) {
                    $query->where('product_supplier.price_recommend', 'like', '%' . request('recommend_price') . '%');
                }

                if (request()->has('status')) {
                    $query->where('product_supplier.status', request('status'));
                }

                if (request()->has('supplier_name')) {
                    $query->where('suppliers.name', 'like', '%' . request('supplier_name') . '%');
                }

                if (request()->has('supplier_quantity')) {
                    $query->where('product_supplier.quantity', request('supplier_quantity'));
                }

                if (request()->has('state')) {
                    $query->where('product_supplier.state', request('state'));
                }

                if (request()->has('updated_at')) {
                    $date = request('updated_at');

                    $from = trim(explode(' - ', $date)[0]);
                    $from = Carbon::createFromFormat('d/m/Y', $from)->startOfDay()->toDateTimeString();

                    $to = trim(explode('-', $date)[1]);
                    $to = Carbon::createFromFormat('d/m/Y', $to)->endOfDay()->toDateTimeString();

                    $query->where('product_supplier.updated_at', '>', $from);
                    $query->where('product_supplier.updated_at', '<', $to);
                }
            })
            ->editColumn('import_price', function ($product) {
                return number_format($product->import_price);
            })
            ->editColumn('vat', function ($product) {
                return number_format($product->vat);
            })
            ->editColumn('saler_price', function ($product) {
                $saler_price = number_format($product->import_price + $product->vat);
                return $saler_price;
            })
            ->editColumn('updated_at', function ($product) {
                $updated_at = Carbon::parse($product->updated_at)->addHour(7);
                return $updated_at;
            })
            ->editColumn('status', function ($product) {
                if ($product->status == 0) {
                    $string = 'Chờ duyệt';
                } else if ($product->status == 1) {
                    $string = 'Hết hàng';
                } else if ($product->status == 2) {
                    $string = 'Ưu tiên lấy hàng';
                } else if ($product->status == 3) {
                    $string = 'Yêu cầu ưu tiên lấy hàng';
                } else if ($product->status == 4) {
                    $string = 'Không ưu tiên lấy hàng';
                }
                return $string;
            })->editColumn('status_product', function ($product) {
                if ($product->status_product == 0) {
                    $string = 'Hết hàng';
                } else if ($product->status_product == 1) {
                    $string = 'Còn hàng';
                } else if ($product->status_product == 2) {
                    $string = 'Đặt hàng';
                }
                return $string;
            })->editColumn('recommend_price', function ($product) {
                return number_format($product->recommend_price);
            })->addColumn('action', function ($product) {
                $string = '';
//                if($product->status == 0) {
//                    $string = '<button data-id = "'.$product->id_product.'" class="btn btn-success checkStatus" id = "checkStatus">Duyệt </button>';
//                }
                if ($product->id_product == 0) {
                    $string .= '<button style = "margin-top:5px" data-id = "' . $product->id . '" class="btn btn-primary connect" id="connect">Liên kết</button>';
                }
                return $string;
            })
            ->make(true);
    }

    protected function getSuppliers(Request $request)
    {

        $product_id = $request->input('product_id');
        $user_id = Sentinel::getUser()->id;
        $products = UserSupportedProvince::join('provinces', 'user_supported_province.region_id', '=', 'provinces.region_id')
            ->join('supplier_supported_province', 'provinces.id', '=', 'supplier_supported_province.province_id')
            ->join('product_supplier', 'supplier_supported_province.supplier_id', '=', 'product_supplier.supplier_id')
            ->join('suppliers', 'product_supplier.supplier_id', '=', 'suppliers.id')
            ->where('user_supported_province.supported_id', $user_id)
            ->where('product_supplier.product_id', $product_id)
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

    public function updateStatus(Request $request)
    {

        $product_supplier_arr = $request->input('product_supplier_id');
        $product_supplier_status = $request->input('status');
        $products = $request->input('product');
        $best_price = $request->input('best_price');

        for ($i = 0; $i < count($product_supplier_arr); $i++) {
            ProductSupplier::find($product_supplier_arr[$i])->update(['status' => $product_supplier_status[$i]]);
            if ($product_supplier_status[$i] == 2) {
                Product::find($products[$i])->forceFill(['best_price' => $best_price[$i]])->save();
            }
        }

        flash()->success('Success!', 'Status successfully updated.');
        return redirect()->back();
    }

    public function updateIdProduct(Request $request)
    {

        $product_supplier_id = $request->input('product_supplier_id');
        $product_id = $request->input('product_id');

        ProductSupplier::find($product_supplier_id)->update(['product_id' => $product_id, 'status' => 2]);

        flash()->success('Success!', 'Status successfully updated.');
        return redirect()->back();
    }

    public function updateDatatables(Request $request)
    {
        $id = $request->input('id');
        $status = $request->input('status');
        $status_product = $request->input('status_product');
        $import_price = $request->input('import_price');
        $supplier_quantity = $request->input('supplier_quantity');
        $recommend_price = $request->input('recommend_price');

        if ($status_product == 'Hết hàng') {
            $status_product = 0;
        } else if ($status_product == 'Còn hàng') {
            $status_product = 1;
        }
//         else if ($status_product == 'Đặt hàng') {
//            $status_product = 2;
//        }

        $product = ProductSupplier::findOrFail($id);
        $product_id = $product->product_id;
        $supplier_id = $product->supplier_id;

        $product->update(['state' => $status_product, 'import_price' => $import_price, 'quantity' => $supplier_quantity, 'price_recommend' => $recommend_price]);

        $sku = Product::where('id',$product->id)->pluck('sku');

        $jsonSend = [
            'product_id' => $product_id,
            'supplier_id' => $supplier_id,
            'import_price' => $import_price,
            'sku' => $sku[0],
            'createdAt' => strtotime($product->updated_at)
        ];
        $messSend = json_encode($jsonSend);
        dispatch(new PublishMessage('teko.sale', 'sale.price.import.update', $messSend));
    }

    public function getList()
    {
        return view('suppliers.list');
    }

    public function suppliersDatables()
    {
        return Supplier::getDatatables();
    }

    public function show(Supplier $supplier)
    {
        $products = ProductSupplier::join('products', 'product_supplier.product_id', '=', 'products.id')
            ->where('supplier_id', $supplier->id)
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('manufacturers', 'products.manufacturer_id', '=', 'manufacturers.id')
            ->select('products.*', 'categories.name as category_name', 'manufacturers.name as manufacture_name', 'product_supplier.state as state')->get();

        return view('suppliers.show', compact('products'));
    }

    public function create()
    {
        $supplier = (new Supplier)->forceFill([
            'status' => true,
        ]);

        $address = (new SupplierAddress)->forceFill([
            'is_default' => true,
        ]);

        return view('suppliers.create', compact('supplier', 'address'));
    }

    public function store()
    {
        $this->validate(request(), [
            'address' => 'required',
            'name' => 'required|max:255',
            'phone' => 'required',
            'tax_number' => 'required',
            'province_id' => 'required',
            'type' => 'required',

        ]);

        $supplier = Supplier::forceCreate([
            'name' => request('name'),
            'code' => strtoupper(request('code')),
            'status' => !!request('status'),
            'phone' => request('phone'),
            'fax' => request('fax'),
            'email' => request('email'),
            'website' => request('website'),
            'tax_number' => request('tax_number'),
            'type' => request('type'),
            'created_by' => Sentinel::getUser()->id
        ]);

        $province = Province::find(request('province_id'));
        $district = District::where('district_id', request('district_id'))->select('name')->first();

        $supplier_address = SupplierAddress::forceCreate([
            'supplier_id' => $supplier->id,
            'province_id' => request('province_id'),
            'district_id' => request('district_id'),
            'province_name' => isset($province->name) ? $province->name : '',
            'district_name' => isset($district->name) ? $district->name : '',
            'address' => request('address',''),
            'addressCode' => request('addressCode',''),
            'contact_name' => request('contact_name',''),
            'contact_mobile' => request('contact_mobile',''),
            'contact_phone' => request('contact_phone',''),
            'contact_email' => request('contact_email',''),
            'status' => !!request('status'),
            'is_default' => !!request('is_default'),
            'created_by' => Sentinel::getUser()->id
        ]);

        $supplier_bank_account = SupplierBankAccount::forceCreate([
            'supplier_id' => $supplier->id,
            'bank_name' => request('bank_name',''),
            'bank_code' => request('bank_code',''),
            'bank_account' => request('bank_account',''),
            'bank_branch' => request('bank_branch',''),
            'bank_province' => request('bank_province',''),
            'bank_account_name' => request('bank_account_name',''),
            'status' => !!request('status'),
            'is_default' => !!request('is_default'),
        ]);

        $supplier_supported_province = SupplierSupportedProvince::forceCreate([
            'supplier_id' => $supplier->id,
            'province_id' => request('province_id',''),
            'status' => !!request('status'),
        ]);

        // MQ

        $jsonSend = [
            "id"        => $supplier->id,
            "name"      => $supplier->name,
            "code"      => $supplier->code,
            "status"    => $supplier->status == true ? 'active' : 'inactive',
            "phone"     => $supplier->phone,
            "fax"       => $supplier->fax,
            "email"     => $supplier->email,
            "website"   => $supplier->website,
            "tax_number"   => $supplier->tax_number,
            "contactName"   => $supplier_address->contact_name,
            "contactMobile"   => $supplier_address->contact_mobile,
            "contactPhone"   => $supplier_address->contact_phone,
            "contactEmail"   => $supplier_address->contact_email,
            "createdAt" => strtotime($supplier->created_at),
            "addresses" => [
                "default" => [
                    "province" => $supplier_address->province_name,
                  "district" => $supplier_address->district_name,
                  "address"  => $supplier_address->address,
                  "addressCode" => $supplier_address->addressCode,
                  "contactName"   => $supplier_address->contact_name,
                  "contactMobile"   => $supplier_address->contact_mobile,
                  "contactPhone"   => $supplier_address->contact_phone,
                  "contactEmail"   => $supplier_address->contact_email,
                ],
                "others" => []
            ],
            "supportedProvince" => [
                $supplier_address->province_name
            ],
            "accounts" => [
                "default" => [
                    "bankAccount" => $supplier_bank_account->bank_account,
                    "bankAccountName" => $supplier_bank_account->bank_account_name,
                    "bankName" => $supplier_bank_account->bank_name,
                    "bankCode" => $supplier_bank_account->bank_code,
                    "bankProvince" => $supplier_bank_account->bank_province,
                    "bankBranch" => $supplier_bank_account->bank_branch,
                ],
                "others" => []
            ]
        ];

        $messSend = json_encode($jsonSend);
        dispatch(new PublishMessage('teko.sale', 'sale.supplier.upsert', $messSend));

        flash()->success('Success!', 'Suppliers successfully created.');
        return redirect()->route('suppliers.getList');
    }

    public function edit(Supplier $supplier)
    {
        $address = $supplier->addresses()->first();

        if(isset($address)){
            $distristList = District::where('province_id', $address->province_id)->get();
        } else {
            $address = new SupplierAddress();
            $distristList = [];
        }

        return view('suppliers.edit', compact('supplier', 'address', 'distristList'));
    }

    public function update(Supplier $supplier)
    {
        $this->validate(request(), [
            'address' => 'required',
            'name' => 'required|max:255',
            'phone' => 'required',
            'tax_number' => 'required',
            'province_id' => 'required',
            'type' => 'required',
        ]);

        $supplier->forceFill([
            'name' => request('name'),
            'code' => strtoupper(request('code')),
            'status' => !!request('status'),
            'phone' => request('phone'),
            'fax' => request('fax'),
            'email' => request('email'),
            'website' => request('website'),
            'tax_number' => request('tax_number'),
            'type' => request('type'),
            'created_by' => Sentinel::getUser()->id
        ])->save();


        $province = Province::find(request('province_id'));
        $district = District::where('district_id', request('district_id'))->select('name')->first();

        $supplier_address = SupplierAddress::where('supplier_id', $supplier->id)->first();
        if (count($supplier_address) > 0) {
            $supplier_address->forceFill([
                'supplier_id' => $supplier->id,
                'province_id' => request('province_id'),
                'district_id' => request('district_id'),
                'province_name' => isset($province->name) ? $province->name : '',
                'district_name' => isset($district->name) ? $district->name : '',
                'address' => request('address',''),
                'addressCode' => request('addressCode',''),
                'contact_name' => request('contact_name',''),
                'contact_mobile' => request('contact_mobile',''),
                'contact_phone' => request('contact_phone',''),
                'contact_email' => request('contact_email',''),
                'status' => !!request('status'),
                'is_default' => !!request('is_default'),
                'updated_by' => Sentinel::getUser()->id
            ])->save();
        } else {
            $supplier_address = SupplierAddress::forceCreate([
                'supplier_id' => $supplier->id,
                'province_id' => request('province_id'),
                'district_id' => request('district_id'),
                'province_name' => isset($province->name) ? $province->name : '',
                'district_name' => isset($district->name) ? $district->name : '',
                'address' => request('address',''),
                'addressCode' => request('addressCode',''),
                'contact_name' => request('contact_name',''),
                'contact_mobile' => request('contact_mobile',''),
                'contact_phone' => request('contact_phone',''),
                'contact_email' => request('contact_email',''),
                'status' => !!request('status'),
                'is_default' => !!request('is_default'),
                'created_by' => Sentinel::getUser()->id
            ]);
        }

        $supplier_bank_account = SupplierBankAccount::where('supplier_id', $supplier->id)->first();

        if (count($supplier_bank_account) > 0) {
            $supplier_bank_account->forceFill([
                'supplier_id' => $supplier->id,
                'bank_name' => request('bank_name',''),
                'bank_code' => request('bank_code',''),
                'bank_account' => request('bank_account',''),
                'bank_branch' => request('bank_branch',''),
                'bank_province' => request('bank_province',''),
                'bank_account_name' => request('bank_account_name',''),
                'status' => !!request('status'),
                'is_default' => !!request('is_default'),
            ])->save();
        } else {
            $supplier_bank_account = SupplierBankAccount::forceCreate([
                'supplier_id' => $supplier->id,
                'bank_name' => request('bank_name',''),
                'bank_code' => request('bank_code',''),
                'bank_account' => request('bank_account',''),
                'bank_branch' => request('bank_branch',''),
                'bank_province' => request('bank_province',''),
                'bank_account_name' => request('bank_account_name',''),
                'status' => !!request('status'),
                'is_default' => !!request('is_default'),
            ]);
        }

        $supplier_supported_province = SupplierSupportedProvince::where('supplier_id', $supplier->id)->first();

        if (count($supplier_supported_province) > 0) {
            $supplier_supported_province->forceFill([
                'supplier_id' => $supplier->id,
                'province_id' => request('province_id'),
                'status' => !!request('status'),
            ])->save();
        } else {
            $supplier_supported_province = SupplierSupportedProvince::forceCreate([
                'supplier_id' => $supplier->id,
                'province_id' => request('province_id',''),
                'status' => !!request('status'),
            ]);
        }

        // MQ

        $jsonSend = [
            "id"        => $supplier->id,
            "name"      => $supplier->name,
            "code"      => $supplier->code,
            "status"    => $supplier->status == true ? 'active' : 'inactive',
            "phone"     => $supplier->phone,
            "fax"       => $supplier->fax,
            "email"     => $supplier->email,
            "website"   => $supplier->website,
            "tax_number"   => $supplier->tax_number,
            "contactName"   => $supplier_address->contact_name,
            "contactMobile"   => $supplier_address->contact_mobile,
            "contactPhone"   => $supplier_address->contact_phone,
            "contactEmail"   => $supplier_address->contact_email,
            "createdAt" => strtotime($supplier->updated_at),
            "addresses" => [
                "default" => [
                    "province" => $supplier_address->province_name,
                    "district" => $supplier_address->district_name,
                    "address"  => $supplier_address->address,
                    "addressCode" => $supplier_address->addressCode,
                    "contactName"   => $supplier_address->contact_name,
                    "contactMobile"   => $supplier_address->contact_mobile,
                    "contactPhone"   => $supplier_address->contact_phone,
                    "contactEmail"   => $supplier_address->contact_email,
                ],
                "others" => []
            ],
            "supportedProvince" => [
                $supplier_address->province_name
            ],
            "accounts" => [
                "default" => [
                    "bankAccount" => $supplier_bank_account->bank_account,
                    "bankAccountName" => $supplier_bank_account->bank_account_name,
                    "bankName" => $supplier_bank_account->bank_name,
                    "bankCode" => $supplier_bank_account->bank_code,
                    "bankProvince" => $supplier_bank_account->bank_province,
                    "bankBranch" => $supplier_bank_account->bank_branch,
                ],
                "others" => []
            ]
        ];

        $messSend = json_encode($jsonSend);
        dispatch(new PublishMessage('teko.sale', 'sale.supplier.upsert', $messSend));

        flash()->success('Success!', 'Suppliers successfully created.');
        return redirect()->route('suppliers.getList');
    }

    public function exportExcel()
    {
    $user_id = Sentinel::getUser()->id;
    $products = UserSupportedProvince::join('provinces', 'user_supported_province.region_id', '=', 'provinces.region_id')
        ->join('supplier_supported_province', 'provinces.id', '=', 'supplier_supported_province.province_id')
        ->join('product_supplier', 'supplier_supported_province.supplier_id', '=', 'product_supplier.supplier_id')
        ->join('suppliers', 'product_supplier.supplier_id', '=', 'suppliers.id')
        ->leftJoin('products', 'product_supplier.product_id', '=', 'products.id')
        ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
        ->leftJoin('manufacturers', 'products.manufacturer_id', '=', 'manufacturers.id')
        ->where('user_supported_province.supported_id', $user_id)
        ->orderBy('product_supplier.updated_at', 'desc')
        ->select(DB::raw('distinct product_supplier.id as id,product_supplier.product_id as id_product,product_supplier.supplier_id as id_supplier,categories.name as cat_name, products.sku as sku,
                    product_supplier.name as product_name,product_supplier.import_price as import_price,product_supplier.status as status,
                    product_supplier.price_recommend as recommend_price, manufacturers.name as manufacturer_name,product_supplier.quantity as supplier_quantity,
                    product_supplier.updated_at as updated_at,product_supplier.state as status_product,suppliers.name as supplier_name'));

            if (request()->has('category_name')) {
                $products->where('categories.name', 'like', '%' . request('category_name') . '%');
            }

            if (request()->has('manufacture_name')) {
                $products->where('manufacturers.name', 'like', '%' . request('manufacture_name') . '%');
            }

            if (request()->has('product_sku')) {
                $products->where('products.sku', 'like', '%' . request('product_sku') . '%');
            }

            if (request()->has('product_name')) {
                $products->where('products.name', 'like', '%' . request('product_name') . '%');
            }

            if (request()->has('product_import_price')) {
                $products->where('product_supplier.import_price', request('product_import_price'));
            }

            if (request()->has('recommend_price')) {
                $products->where('product_supplier.price_recommend', request('recommend_price'));
            }

            if (request()->has('status')) {
                $products->where('product_supplier.status', request('status'));
            }

            if (request()->has('supplier_name')) {
                $products->where('suppliers.name', 'like', '%' . request('supplier_name') . '%');
            }

            if (request()->has('supplier_quantity')) {
                $products->where('product_supplier.quantity', request('supplier_quantity'));
            }

            if (request()->has('state')) {
                $products->where('product_supplier.state', request('state'));
            }

            if (request()->has('updated_at')) {
                $date = request('updated_at');

                $from = trim(explode(' - ', $date)[0]);
                $from = Carbon::createFromFormat('d/m/Y', $from)->startOfDay()->toDateTimeString();

                $to = trim(explode('-', $date)[1]);
                $to = Carbon::createFromFormat('d/m/Y', $to)->endOfDay()->toDateTimeString();

                $products->where('product_supplier.updated_at', '>', $from);
                $products->where('product_supplier.updated_at', '<', $to);
            }

    $products = $products->get();

    Excel::create('supplier_product', function ($excel) use($products) {
    $excel->sheet('Sheet 1',function ($sheet) use ($products) {
            $sheet->fromArray($products);
        });
    })->store('xlsx','exports');
    return [
        'success' => true,
        'path' => 'http://'.request()->getHttpHost().'/exports/supplier_product.xlsx'
    ];
    }

    public function importExcel()
    {
        $this->validate(request(), [
            'file'=>'required|max:50000|mimes:xlsx'
        ]);

        $file = request()->file('file');
        Excel::load($file,function($reader) {
              $reader->each(function ($sheet){
                  $supplier_product = ProductSupplier::where('product_id', $sheet->id_product)->where('supplier_id', $sheet->id_supplier)->first();
                  if(count($supplier_product) > 0) {
                      $supplier_product->forceFill([
                          'name' => $sheet->product_name ? $sheet->product_name : '',
                          'code' => request('code',''),
                          'import_price' => $sheet->import_price ? $sheet->import_price : 0,
                          'price_recommend' => $sheet->recommend_price ? $sheet->recommend_price : 0,
                          'state' => $sheet->status_product ? $sheet->status_product : 1,
                          'updated_by' => Sentinel::getUser()->id,
                      ])->save();
                  } else {
                      ProductSupplier::forceCreate([
                          'product_id' => $sheet->id_product ? $sheet->id_product : 0,
                          'supplier_id' =>  $sheet->id_supplier ? $sheet->id_supplier : 0,
                          'name' => $sheet->product_name ? $sheet->product_name : '',
                          'code' => request('code',''),
                          'import_price' => $sheet->import_price ? $sheet->import_price : 0,
                          'price_recommend' => $sheet->recommend_price ? $sheet->recommend_price : 0,
                          'state' => $sheet->status_product ? $sheet->status_product : 1,
                          'created_by' => Sentinel::getUser()->id,
                      ]);
                  }
              });
          });

        flash()->success('Success!', 'Product Supplier successfully updated.');

        return redirect()->back();
    }

}
