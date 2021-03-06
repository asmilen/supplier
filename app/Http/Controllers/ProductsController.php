<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Product;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Events\ProductUpserted;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categoriesList = Category::getActiveList();

        $manufacturersList = Manufacturer::getActiveList();

        return view('products.index', compact('categoriesList', 'manufacturersList'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (request()->has('category_id')) {
            $category = Category::find(request('category_id'));
        }

        return view('products.create', compact('category'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return $product;
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
        $channels = array_keys(array_filter(request('channels', []), function ($value, $key) {
            return $value;
        }, ARRAY_FILTER_USE_BOTH));

        Validator::make(request()->all(), [
            'name' => 'required|max:255|unique:products,name,' . $product->id,
            'image_base64' => 'nullable|image_base64'
        ], [
            'name.unique' => 'Tên sản phẩm đã tồn tại.',
            'image_base64.image_base64' => 'Ảnh phải có định dạnh jpg/png và kích thước không quá 2MB',
        ])->after(function ($validator) use ($channels) {
            if (empty($channels)) {
                $validator->errors()->add('channels', 'Bạn chưa chọn kênh bán hàng.');
            }
        })->validate();

        $product->forceFill([
            'name' => trim(request('name')),
            'source_url' => trim(request('source_url')),
            'description' => request('description'),
            'status' => !! request('status'),
        ])->save();

        $product
            ->setChannels($channels)
            ->setFeaturedImage(request('image_base64'));

        event(new ProductUpserted($product));

        if ($product->needToOffOnMagento()) {
            $product->setOffOnMagento();
        }

        return $product;
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

    public function listing()
    {
        $sorting = request('sorting', 'id');

        $direction = request('direction', 'desc');

        $page = request('page', 1);

        $limit = request('limit', 25);

        $builder = Product::where(function ($query) {
            if (! empty(request('q'))) {
                $query->where(function ($q) {
                    $q->where('id', 'like', '%'.request('q').'%')
                        ->orWhere('code', 'like', '%'.request('q').'%')
                        ->orWhere('sku', 'like', '%'.request('q').'%')
                        ->orWhere('name', 'like', '%'.request('q').'%');
                });
            }

            if (! empty(request('category_id'))) {
                $query->where('category_id', request('category_id'));
            }

            if (! empty(request('manufacturer_id'))) {
                $query->where('manufacturer_id', request('manufacturer_id'));
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

        $products = $builder
            ->orderBy('status', 'desc')
            ->orderBy($sorting, $direction)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return response()->json([
            'data' => $products,
            'total_items' => $totalItems,
            'all' => Product::count(),
        ]);
    }
}
