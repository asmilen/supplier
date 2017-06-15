<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Color;
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
            'manufacturer_id' => 'required',
            'name' => 'required|max:255|unique:products',
            'code' => 'alpha_num|max:255',
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

        $product = Product::forceCreate([
            'category_id' => request('category_id'),
            'manufacturer_id' => request('manufacturer_id'),
            'color_id' => request('color_id',0),
            'type' => request('type') == 'simple' ? 0 : 1,
            'parent_id' => request('parent_id', 0),
            'name' => request('name'),
            'status' => !! request('status'),
            'description' => request('description'),
            'attributes' => json_encode(request('attributes', [])),
        ]);

        if (empty($code)) {
            $code = $product->id;
        }

        $product->forceFill([
            'code' => $code,
            'sku' => $this->generateSku(request('category_id'), request('manufacturer_id'), $code, request('color_id')),
        ])->save();

        if(request('type') == 'simple') {
            dispatch(new PublishMessage('teko.sale', 'sale.product.upsert', json_encode([
                'id' => $product->id,
                'categoryId' => $product->category_id,
                'brandId' => $product->manufacturer_id,
                'type' => 'simple',
                'sku' => $product->sku,
                'name' => $product->name,
                'skuIdentifier' => $product->code,
                'status' => $product->status ? 'active' : 'inactive',
                'sourceUrl' => $product->source_url,
                'createdAt' => strtotime($product->created_at),
            ])));
        }

        flash()->success('Success!', 'Product successfully created.');

        return $product;
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
        $productChilds = Product::where('parent_id', $product->id)->get();

        return view('products.edit', compact('product', 'productChilds'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Product $product)
    {
        $code = strtoupper(request('code'));

        Validator::make(request()->all(), [
            'category_id' => 'required',
            'manufacturer_id' => 'required',
            'name' => 'required|max:255|unique:products,name,'.$product->id,
            'code' => 'alpha_num|max:255',
        ], [
            'name.unique' => 'Tên nhà sản phẩm đã tồn tại.',
        ])->after(function ($validator) use ($product, $code) {
            if (! empty($code)) {
                $check = Product::where('category_id', request('category_id'))
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
            'color_id' => request('color_id', 0),
            'parent_id' => request('parent_id', 0),
            'name' => request('name'),
            'code' => $code,
            'sku' => $this->generateSku(request('category_id'), request('manufacturer_id'), $code, request('color_id')),
            'status' => !! request('status'),
            'description' => request('description'),
            'attributes' => json_encode(request('attributes', [])),
        ])->save();


        dispatch(new PublishMessage('teko.sale', 'sale.product.upsert', json_encode([
            'id' => $product->id,
            'categoryId' => $product->category_id,
            'brandId' => $product->manufacturer_id,
            'sku' => $product->sku,
            'name' => $product->name,
            'skuIdentifier' => $product->code,
            'status' => $product->status ? 'active' : 'inactive',
            'sourceUrl' => $product->source_url,
            'createdAt' => strtotime($product->created_at),
        ])));

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

    protected function generateSku($categoryId, $manufacturerId, $code, $colorId = null)
    {
        $category = Category::findOrFail($categoryId);

        $manufacturer = Manufacturer::findOrFail($manufacturerId);

        $sku = $category->code.'-'.$manufacturer->code.'-'.$code;

        $color = Color::find($colorId);

        if ($color) {
            $sku .= '-'.$color->code;
        }

        return $sku;
    }

    public function addChilds()
    {
        if (request()->has('productIds')) {
            $productIds = request('productIds');

            foreach ($productIds as $key => $productId) {
                Product::find($productId)->forceFill(['parent_id' => request('productId')])->save();
            }
        }
        return response()->json(['status' => 'success']);
    }

    public function destroyChilds(){
        Product::find(request('productId'))->forceFill(['parent_id' => 0])->save();
        return response()->json(['status' => 'success']);
    }
}
