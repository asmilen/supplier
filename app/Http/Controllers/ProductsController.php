<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Product;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Jobs\PublishMessage;

class ProductsController extends Controller
{
    public function __construct()
    {
        view()->share('categoriesList', Category::getActiveList());
        view()->share('manufacturersList', Manufacturer::getActiveList());
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
        if (empty(request('code'))) {
            $code = Product::where('category_id', request('category_id'))
                ->where('manufacturer_id', request('manufacturer_id'))
                ->count() + 1;
        } else {
            $code = strtoupper(request('code'));
        }

        Validator::make(request()->all(), [
            'category_id' => 'required',
            'manufacturer_id' => 'required',
            'name' => 'required|max:255|unique:products',
            'code' => 'alpha_num|max:255',
        ], [
            'name.unique' => 'Tên nhà sản phẩm đã tồn tại.',
        ])->after(function ($validator) use ($code) {
            $check = Product::where('category_id', request('category_id'))
                ->where('manufacturer_id', request('manufacturer_id'))
                ->where('code', $code)
                ->first();

            if ($check) {
                $validator->errors()->add('code', 'Mã sản phẩm này đã tồn tại.');
            }
        })->validate();

        $product = Product::forceCreate([
            'category_id' => request('category_id'),
            'manufacturer_id' => request('manufacturer_id'),
            'name' => request('name'),
            'code' => $code,
            'sku' => $this->generateSku(request('category_id'), request('manufacturer_id'), $code),
            'status' => !! request('status'),
            'description' => request('description'),
        ]);


        $jsonSend = [
            "id"            => $product->id,
            "categoryId"      => $product->category_id,
            "brandId"      => $product->manufacturer_id,
            "type" => "simple",
            "sku"      => $product->sku,
            "name"      => $product->name,
            "skuIdentifier"      => $product->code,
            "status"    => $product->status == true ? 'active' : 'inactive',
            "sourceUrl"      => $product->source_url,
            "createdAt" => strtotime($product->created_at)
        ];
        $messSend = json_encode($jsonSend);

        dispatch(new PublishMessage('test-exchange', 'sale.product.upsert', $messSend));

        flash()->success('Success!', 'Product successfully created.');

        return redirect()->route('products.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Product $product)
    {
        if (empty(request('code'))) {
            $code = Product::where('category_id', request('category_id'))
                ->where('manufacturer_id', request('manufacturer_id'))
                ->count();
        } else {
            $code = strtoupper(request('code'));
        }

        Validator::make(request()->all(), [
            'category_id' => 'required',
            'manufacturer_id' => 'required',
            'name' => 'required|max:255|unique:products,name,'.$product->id,
            'code' => 'alpha_num|max:255',
        ], [
            'name.unique' => 'Tên nhà sản phẩm đã tồn tại.',
        ])->after(function ($validator) use ($product, $code) {
            $check = Product::where('category_id', request('category_id'))
                ->where('manufacturer_id', request('manufacturer_id'))
                ->where('code', $code)
                ->where('id', '<>', $product->id)
                ->first();

            if ($check) {
                $validator->errors()->add('code', 'Mã sản phẩm này đã tồn tại.');
            }
        })->validate();

        $product->forceFill([
            'category_id' => request('category_id'),
            'manufacturer_id' => request('manufacturer_id'),
            'name' => request('name'),
            'code' => $code,
            'sku' => $this->generateSku(request('category_id'), request('manufacturer_id'), $code),
            'status' => !! request('status'),
            'description' => request('description'),
        ])->save();

        $jsonSend = [
            "id"            => $product->id,
            "categoryId"      => $product->category_id,
            "brandId"      => $product->manufacturer_id,
            "sku"      => $product->sku,
            "name"      => $product->name,
            "skuIdentifier"      => $product->code,
            "status"    => $product->status == true ? 'active' : 'inactive',
            "sourceUrl"      => $product->source_url,
            "createdAt" => strtotime($product->created_at)
        ];
        $messSend = json_encode($jsonSend);

        dispatch(new PublishMessage('test-exchange', 'sale.product.upsert', $messSend));

        flash()->success('Success!', 'Product successfully updated.');

        return redirect()->route('products.index');
    }

    public function getDatatables()
    {
        return Product::getDatatables();
    }

    protected function generateSku($categoryId, $manufacturerId, $code)
    {
        $category = Category::findOrFail($categoryId);

        $manufacturer = Manufacturer::findOrFail($manufacturerId);

        return $category->code.'-'.$manufacturer->code.'-'.$code;
    }
}
