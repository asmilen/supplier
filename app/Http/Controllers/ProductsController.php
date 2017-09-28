<?php

namespace App\Http\Controllers;

use App\Jobs\OffProductToMagento;
use Validator;
use App\Models\Color;
use App\Models\Product;
use Sentinel;
use App\Models\ProductSupplier;
use App\Models\Category;
use App\Models\Manufacturer;
use Intervention\Image\Facades\Image as Image;

class ProductsController extends Controller
{
    public function __construct()
    {
        view()->share('categoriesList', Category::getActiveList());
        view()->share('manufacturersList', Manufacturer::getActiveList());
        view()->share('colorsList', Color::getActiveList());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('products.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $product = (new Product)->forceFill([
            'status' => true,
        ]);

        return view('products.create', compact('product'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $code = strtoupper(request('code'));
        Validator::make(request()->all(), [
            'category_id' => 'required',
            'channels' => 'required',
            'manufacturer_id' => 'required',
            'name' => 'required|max:255|unique:products',
            //'image' => 'image|mimes:jpg,png,jpeg|max:2000',
            'code' => 'alpha_num|max:255',
        ], [
            'name.unique' => 'Tên sản phẩm đã tồn tại.',
            'name.required' => 'Bạn chưa nhập tên sản phẩm.',
            'name.max' => 'Tên sản phẩm quá dài, tối đa 255 ký tự.',
            'channels.required' => 'Bạn chưa chọn kênh bán hàng.',
            'category_id.required' => 'Bạn chưa chọn danh mục.',
            'manufacturer_id.required' => 'Bạn chưa chọn nhà sản xuất.',
            'code.alpha_num' => 'Mã sản phẩm phải là số.',
            'code.max' => 'Mã sản phẩm quá dài, tối đa 255 ký tự.',
        ])->after(function ($validator) use ($code) {
            if (!empty($code)) {
                $check = Product::where('category_id', request('category_id'))
                    ->where('manufacturer_id', request('manufacturer_id'))
                    ->where('code', $code)
                    ->first();

                if ($check) {
                    $validator->errors()->add('code', 'Mã sản phẩm này đã tồn tại.');
                }
            }
        })->validate();

//        $file = request('image');

//        $filename = md5(uniqid() . '_' . time()) . '.' . $file->getClientOriginalExtension();
//        Image::make($file->getRealPath())->save(storage_path('app/public/' . $filename));


        $channelChoose = [];
        foreach (request('channels') as $key => $channel) {
            if ($channel) array_push($channelChoose, $key);
        }

        $product = Product::forceCreate([
            'category_id' => request('category_id'),
            'manufacturer_id' => request('manufacturer_id'),
            'color_id' => request('color_id', 0),
            'type' => request('type') == 'simple' ? 0 : 1,
            'parent_id' => request('parent_id', 0),
            'name' => request('name'),
            'status' => filter_var(request('status'), FILTER_VALIDATE_BOOLEAN),
//            'image' => url('/') . '/storage/' . $filename,
            'description' => request('description'),
            'attributes' => json_encode(request('attributes', [])),
            'channel' => implode(",", $channelChoose),
        ]);

        flash()->success('Success!', 'Product successfully created.');

        return $product;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Product $product)
    {
        $user = Sentinel::getUser();

        $code = strtoupper(request('code'));

        $rules = [
            'name' => 'required|max:255|unique:products,name,' . $product->id,
        ];

        if (request()->file('image')) {
            $rules['image'] = 'image|mimes:jpg,png,jpeg|max:2000';
        }

        $channelChoose = [];
        foreach (explode(',', request('channel')) as $key => $channel) {
            if ($channel == 'true') {
                array_push($channelChoose, $key);
            }
        }

        if (filter_var(request('status'), FILTER_VALIDATE_BOOLEAN) == false || !in_array("2", $channelChoose)){
            $productSuppliers = ProductSupplier::where('product_id', $product->id)->get();
            foreach ($productSuppliers as $productSupplier){
                dispatch(new OffProductToMagento($product, 0, $user, $productSupplier->region_id));
            }
        }

        Validator::make(request()->all(), $rules, [
            'name.unique' => 'Tên sản phẩm đã tồn tại.',
            'channel.max' => 'Bạn chưa chọn kênh bán hàng.',
        ])->after(function ($validator) use ($channelChoose) {
            if (!$channelChoose) {
                $validator->errors()->add('channel', 'Bạn chưa chọn kênh bán hàng.');
            }
        })->validate();

        $product->forceFill([
            'parent_id' => request('parent_id', 0),
            'name' => request('name'),
            'status' => filter_var(request('status'), FILTER_VALIDATE_BOOLEAN),
            'description' => request('description'),
            'attributes' => json_encode(request('attributes', [])),
            'channel' => implode(",", $channelChoose),
        ])->save();

        if (request()->file('image')) {
            $file = request('image');
            $filename = md5(uniqid() . '_' . time()) . '.' . $file->getClientOriginalExtension();
            Image::make($file->getRealPath())->save(storage_path('app/public/' . $filename));

            $product->forceFill([
                'image' => url('/') . '/storage/' . $filename,
            ])->save();
        }

        flash()->success('Success!', 'Product successfully updated.');

        return $product;
    }

    public function getDatatables()
    {
        return Product::getDatatables();
    }

    public function getProductInCombo()
    {
        $productIds = request('productIds', []);

        return Product::getProductInCombo($productIds);
    }

    public function getSimpleProduct()
    {
        return Product::getSimpleProduct();
    }

    public function addChild(Product $product)
    {
        $productChild = Product::findOrFail(request('productChild'));
        $productChild->forceFill(['parent_id' => $product->id])->save();

        return response()->json(['status' => 'success']);
    }

    public function removeChild(Product $product, $childId)
    {
        $productChild = Product::findOrFail($childId);
        $productChild->forceFill(['parent_id' => 0])->save();

        return response()->json(['status' => 'success']);
    }

    public function toggleStatus(Product $product)
    {
        $user = Sentinel::getUser();

        if ($product->status){
            $productSuppliers = ProductSupplier::where('product_id', $product->id)->get();
            foreach ($productSuppliers as $productSupplier){
                dispatch(new OffProductToMagento($product, 0, $user, $productSupplier->region_id));
            }
        }

        $product->forceFill(['status' => !$product->status])->save();
    }
}
