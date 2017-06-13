<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\ProductAttribute;

class ProductAttributeController extends Controller
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
        return view('productAttributes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $product = (new ProductAttribute)->forceFill([
            'status' => true,
        ]);

        return view('productAttributes.create', compact('product'));
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
            'manufacturer_id' => 'required',
            'name' => 'required|max:255|unique:products',
            'code' => 'alpha_num|max:255',
            'price' => 'required',
        ], [
            'name.unique' => 'Tên nhà sản phẩm đã tồn tại.',
        ])->after(function ($validator) use ($code) {
            if (! empty($code)) {
                $check = Product::where('category_id', request('category_id'))
                    ->where('manufacturer_id', request('manufacturer_id'))
                    ->where('code', $code)
                    ->first();

                if ($check) {
                    $validator->errors()->add('code', 'Mã sản phẩm này đã tồn tại.');
                }
            }
        })->validate();

        $product = ProductAttribute::forceCreate([
            'category_id' => request('category_id'),
            'manufacturer_id' => request('manufacturer_id'),
            'name' => request('name'),
            'status' => !! request('status'),
            'description' => request('description')
        ]);

        if (empty($code)) {
            $code = $product->id;
        }

        $product->forceFill([
            'code' => $code,
            'sku' => $this->generateSku(request('category_id'), request('manufacturer_id'), $code),
        ])->save();

        flash()->success('Success!', 'Product successfully created.');

        return $product;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(ProductAttribute $product)
    {
        return view('productAttributes.edit', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductAttribute $product)
    {
        return view('productAttributes.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(ProductAttribute $product)
    {
        $code = strtoupper(request('code'));

        Validator::make(request()->all(), [
            'category_id' => 'required',
            'manufacturer_id' => 'required',
            'name' => 'required|max:255|unique:products,name,'.$product->id,
            'code' => 'alpha_num|max:255',
            'price' => 'required',
        ], [
            'name.unique' => 'Tên nhà sản phẩm đã tồn tại.',
        ])->after(function ($validator) use ($product, $code) {
            if (! empty($code)) {
                $check = ProductAttribute::where('category_id', request('category_id'))
                    ->where('manufacturer_id', request('manufacturer_id'))
                    ->where('code', $code)
                    ->where('id', '<>', $product->id)
                    ->first();

                if ($check) {
                    $validator->errors()->add('code', 'Mã sản phẩm này đã tồn tại.');
                }
            }
        })->validate();

        if (empty($code)) {
            $code = $product->id;
        }

        $product->forceFill([
            'category_id' => request('category_id'),
            'manufacturer_id' => request('manufacturer_id'),
            'color_id' => request('color_id') ? request('color_id') : 0,
            'name' => request('name'),
            'code' => $code,
            'sku' => $this->generateSku(request('category_id'), request('manufacturer_id'), $code, request('color_id')),
            'status' => !! request('status'),
            'description' => request('description')
        ])->save();

        flash()->success('Success!', 'Product successfully updated.');

        return $product;
    }

    public function getDatatables()
    {
        return ProductAttribute::getDatatables();
    }

    protected function generateSku($categoryId, $manufacturerId, $code)
    {
        $category = Category::findOrFail($categoryId);

        $manufacturer = Manufacturer::findOrFail($manufacturerId);

        return $category->code.'-'.$manufacturer->code.'-'.$code;
    }
}
