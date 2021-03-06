<?php

namespace App\Http\Controllers;


use DB;
use Auth;
use Excel;
use Response;
use Sentinel;
use Exception;
use Validator;
use Datatables;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\District;
use App\Models\Province;
use App\Models\Supplier;
use App\Jobs\PublishMessage;
use Illuminate\Http\Request;
use App\Models\LogOffSupplier;
use App\Models\ProductSupplier;
use App\Models\SupplierAddress;
use App\Events\SupplierUpserted;
use App\Jobs\OffProductToMagento;
use App\Jobs\UpdatePriceToMagento;
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
        return view('suppliers.index');
    }

    public function listing()
    {
        $sorting = request('sorting', 'id');

        $direction = request('direction', 'desc');

        $page = request('page', 1);

        $limit = request('limit', 25);

        $builder = Supplier::where(function ($query) {
            if (! empty(request('q'))) {
                $query->where(function ($q) {
                    $q->where('id', request('q'))
                        ->orWhere('code', 'like', '%'.request('q').'%')
                        ->orWhere('name', 'like', '%'.request('q').'%')
                        ->orWhere('full_name', 'like', '%'.request('q').'%');
                });
            }

            if (! empty(request('status'))) {
                if (request('status') == 'active') {
                    $query->active();
                } elseif (request('status') == 'inactive') {
                    $query->inactive();
                }
            }
        });

        $totalItems = $builder->count();

        $suppliers = $builder
            ->orderBy('status', 'desc')
            ->orderBy($sorting, $direction)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return response()->json([
            'data' => $suppliers,
            'total_items' => $totalItems,
            'all' => Supplier::count(),
        ]);
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store()
    {
        $this->validate(request(), [
            'name' => 'required|max:255',
            'full_name' => 'required|max:255',
            'code' => 'required|max:255',
            'phone' => 'required',
            'email' => 'required|email|max:255|unique:suppliers',
            'tax_number' => 'required',
            'type' => 'required',
            'sup_type' => 'required',
            'price_active_time' => 'required|numeric',
            'province_id' => 'required',
            'district_id' => 'required',
            'address' => 'required|max:255',
        ], [
            'name.required' => 'Vui lòng nhập tên.',
            'full_name.required' => 'Vui lòng nhập tên đầy đủ.',
            'name.max' => 'Tên của bạn quá dài, tối đa 255 kí tự.',
            'address.max' => 'Địa chỉ nhà cung cấp quá dài, tối đa 255 kí tự.',
            'address.required' => 'Vui lòng nhập địa chỉ.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.numeric' => 'Số điện thoại không được chứa ký tự.',
            'fax.numeric' => 'Số fax không được chứa ký tự.',
            'fax.required' => 'Vui lòng nhập số fax.',
            'tax_number.required' => 'Vui lòng nhập số tax.',
            'province_id.required' => 'Vui lòng nhập tỉnh thành.',
            'type.required' => 'Vui lòng nhập loại hóa đơn.',
            'sup_type.required' => 'Vui lòng nhập loại nhà cung cấp.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Vui lòng nhập đúng định dạng email.',
            'email.max' => 'Email quá dài, tối đa 255 kí tự.',
            'email.unique' => 'Email đã tồn tại.',
        ]);

        $supplier = Supplier::forceCreate([
            'name' => request('name'),
            'full_name' => request('full_name'),
            'code' => strtoupper(request('code')),
            'phone' => request('phone'),
            'fax' => request('fax'),
            'email' => request('email'),
            'website' => request('website'),
            'tax_number' => request('tax_number'),
            'type' => request('type', 0),
            'sup_type' => request('sup_type', 1),
            'price_active_time' => request('price_active_time', 0) * 24,
            'status' => !! request('status'),
        ])->addDefaultAddress([
            'province_id' => request('province_id'),
            'district_id' => request('district_id'),
            'address' => request('address', ''),
            'addressCode' => request('addressCode', ''),
            'contact_name' => request('contact_name', ''),
            'contact_mobile' => request('contact_mobile', ''),
            'contact_phone' => request('contact_phone', ''),
            'contact_email' => request('contact_email', ''),
        ])->addSupportedProvince(request('province_id'));

        if (! empty(request('bank_account')) && ! empty(request('bank_account_name'))) {
            $supplier->addDefaultBankAccount([
                'bank_account' => request('bank_account', ''),
                'bank_account_name' => request('bank_account_name', ''),
                'bank_name' => request('bank_name', ''),
                'bank_code' => request('bank_code', ''),
                'bank_branch' => request('bank_branch', ''),
                'bank_province' => request('bank_province', ''),
            ]);
        }

        flash()->success('Success!', 'Suppliers successfully created.');

        event(new SupplierUpserted($supplier));

        return $supplier;
    }

    public function show(Supplier $supplier)
    {
        return $supplier;
    }

    public function update(Supplier $supplier)
    {
        $this->validate(request(), [
            'name' => 'required|max:255',
            'full_name' => 'required|max:255',
            'code' => 'required|max:255',
            'phone' => 'required',
            'email' => 'required|email|max:255|unique:suppliers,id,'.$supplier->id,
            'tax_number' => 'required',
            'type' => 'required',
            'sup_type' => 'required',
            'price_active_time' => 'required|numeric',
        ], [
            'name.required' => 'Vui lòng nhập tên.',
            'full_name.required' => 'Vui lòng nhập tên đầy đủ.',
            'name.max' => 'Tên của bạn quá dài, tối đa 255 kí tự.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.numeric' => 'Số điện thoại không được chứa ký tự.',
            'fax.numeric' => 'Số fax không được chứa ký tự.',
            'fax.required' => 'Vui lòng nhập số fax.',
            'tax_number.required' => 'Vui lòng nhập số tax.',
            'type.required' => 'Vui lòng nhập loại hóa đơn.',
            'sup_type.required' => 'Vui lòng nhập loại nhà cung cấp.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Vui lòng nhập đúng định dạng email.',
            'email.max' => 'Email quá dài, tối đa 255 kí tự.',
            'email.unique' => 'Email đã tồn tại.',
        ]);

        $supplier->forceFill([
            'name' => request('name'),
            'full_name' => request('full_name'),
            'code' => strtoupper(request('code')),
            'phone' => request('phone'),
            'fax' => request('fax'),
            'email' => request('email'),
            'website' => request('website'),
            'tax_number' => request('tax_number'),
            'type' => request('type', 0),
            'sup_type' => request('sup_type', 1),
            'price_active_time' => request('price_active_time', 0) * 24,
            'status' => !! request('status'),
        ])->save();

        event(new SupplierUpserted($supplier));

        flash()->success('Success!', 'Suppliers successfully updated.');

        return $supplier;
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
            'from_date' => 'required',
            'to_date' => 'required',
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
            'from_date.required' => 'Hãy nhập Ngày bắt đầu hiệu lực giá',
            'to_date.required' => 'Hãy nhập Ngày kết thúc hiệu lực giá',
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
                if (isset($file)) {
                    $file = request()->file('image');
                    $filename = md5(uniqid() . '_' . time()) . '.' . $file->getClientOriginalExtension();
                    Image::make($file->getRealPath())->save(storage_path('app/public/' . $filename));
                }

                $data = [
                    'product_id' => $request->input('product_id'),
                    'supplier_id' => $request->input('supplier_id'),
                    'import_price' => $request->input('import_price'),
                    'from_date' => Carbon::createFromFormat('d/m/Y', $request->input('from_date'))->startOfDay(),
                    'to_date' => Carbon::createFromFormat('d/m/Y', $request->input('to_date'))->endOfDay(),
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
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $supplier_min_quantity = $request->input('supplier_min_quantity');
        $recommend_price = $request->input('recommend_price');

        if ($status_product == 'Hết hàng') {
            $status_product = 0;
        } else if ($status_product == 'Còn hàng') {
            $status_product = 1;
        }

        $product = ProductSupplier::findOrFail($id);
        $product_id = $product->product_id;
        $supplier_id = $product->supplier_id;

        $product->update([
            'state' => $status_product,
            'import_price' => $import_price,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'quantity' => $supplier_min_quantity,
            'price_recommend' => $recommend_price,
        ]);

        $sku = Product::where('id', $product->product_id)->pluck('sku');

        $jsonSend = [
            'product_id' => $product_id,
            'supplier_id' => $supplier_id,
            'import_price' => $import_price,
            'sku' => $sku[0],
            'createdAt' => strtotime($product->updated_at)
        ];

        dispatch(new PublishMessage('teko.sale', 'sale.price.import.update', $jsonSend));

    }

    public function edit(Supplier $supplier)
    {
        $address = $supplier->addresses()->first();

        if (isset($address)) {
            $distristList = District::where('province_id', $address->province_id)->get();
        } else {
            $address = new SupplierAddress();
            $distristList = [];
        }

        return view('suppliers.edit', compact('supplier', 'address', 'distristList'));
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
                    product_supplier.price_recommend as recommend_price, manufacturers.name as manufacturer_name,product_supplier.min_quantity as supplier_min_quantity,
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

        if (request()->has('supplier_min_quantity')) {
            $products->where('product_supplier.min_quantity', request('supplier_min_quantity'));
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

        Excel::create('supplier_product', function ($excel) use ($products) {
            $excel->sheet('Sheet 1', function ($sheet) use ($products) {
                $sheet->fromArray($products);
            });
        })->store('xlsx', 'exports');
        return [
            'success' => true,
            'path' => 'http://' . request()->getHttpHost() . '/exports/supplier_product.xlsx'
        ];
    }

    public function importExcel()
    {
        $this->validate(request(), [
            'file' => 'required|max:50000|mimes:xlsx'
        ]);

        $file = request()->file('file');
        Excel::load($file, function ($reader) {
            $reader->each(function ($sheet) {
                $supplier_product = ProductSupplier::where('product_id', $sheet->id_product)->where('supplier_id', $sheet->id_supplier)->first();
                if (count($supplier_product) > 0) {
                    $supplier_product->forceFill([
                        'name' => $sheet->product_name ? $sheet->product_name : $supplier_product->name,
                        'code' => request('code', ''),
                        'import_price' => $sheet->import_price ? $sheet->import_price : $supplier_product->import_price,
                        'price_recommend' => $sheet->recommend_price ? $sheet->recommend_price : $supplier_product->price_recommend,
                        'state' => $sheet->status_product ? $sheet->status_product : 1,
                        'updated_by' => Sentinel::getUser()->id,
                    ])->save();
                } else {
                    ProductSupplier::forceCreate([
                        'product_id' => $sheet->id_product ? $sheet->id_product : 0,
                        'supplier_id' => $sheet->id_supplier ? $sheet->id_supplier : 0,
                        'name' => $sheet->product_name ? $sheet->product_name : '',
                        'code' => request('code', ''),
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

    public function updateValidTime()
    {
        try {
            $user_id = Sentinel::getUser()->id;

            $affected = DB::table('user_supported_province')->join('provinces', 'user_supported_province.region_id', '=', 'provinces.region_id')
                ->join('supplier_supported_province', 'provinces.id', '=', 'supplier_supported_province.province_id')
                ->join('product_supplier', 'supplier_supported_province.supplier_id', '=', 'product_supplier.supplier_id')
                ->join('suppliers', 'product_supplier.supplier_id', '=', 'suppliers.id')
                ->leftJoin('products', 'product_supplier.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('manufacturers', 'products.manufacturer_id', '=', 'manufacturers.id')
                ->where('user_supported_province.supported_id', $user_id)
                ->where(function ($query) {

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

                    if (request()->has('recommend_price')) {
                        $query->where('product_supplier.price_recommend', 'like', '%' . request('recommend_price') . '%');
                    }

                    if (request()->has('status')) {
                        $query->where('product_supplier.status', request('status'));
                    }

                    if (request()->has('supplier_name')) {
                        $query->where('suppliers.name', 'like', '%' . request('supplier_name') . '%');
                    }

                    if (request()->has('supplier_min_quantity')) {
                        $query->where('product_supplier.min_quantity', request('supplier_min_quantity'));
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

                    if (request()->has('to_date')) {
                        $date = request('to_date');

                        $from = trim(explode(' - ', $date)[0]);
                        $from = Carbon::createFromFormat('d/m/Y', $from)->startOfDay()->toDateTimeString();

                        $to = trim(explode('-', $date)[1]);
                        $to = Carbon::createFromFormat('d/m/Y', $to)->endOfDay()->toDateTimeString();

                        $query->where('product_supplier.to_date', '>', $from);
                        $query->where('product_supplier.to_date', '<', $to);
                    }
                })
                ->update([
                    'product_supplier.to_date' => Carbon::createFromFormat('m/d/Y', request('valid_time'))->endOfDay(),
                    'product_supplier.updated_at' => Carbon::now(),
                ]);

            return response()->json([
                'type' => 'success',
                'title' => 'Success!',
                'message' => 'Cập nhật thành công ' . $affected . ' Sản phẩm',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'type' => 'error',
                'title' => 'Error!',
                'message' => 'Cập nhật thất bại',
            ]);
        }
    }
}
