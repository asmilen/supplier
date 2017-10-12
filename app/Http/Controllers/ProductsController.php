<?php

namespace App\Http\Controllers;

use Sentinel;
use Validator;
use App\Models\Color;
use App\Models\Product;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Events\ProductUpserted;
use App\Models\ProductSupplier;
use App\Jobs\OffProductToMagento;
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
        if (request()->has('category_id')) {
            $category = Category::find(request('category_id'));
        }

        return view('products.create', compact('category'));
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
            'code' => 'alpha_num|max:255',
            'imageBase64' => 'required'
        ], [
            'name.unique' => 'Tên sản phẩm đã tồn tại.',
            'name.required' => 'Bạn chưa nhập tên sản phẩm.',
            'name.max' => 'Tên sản phẩm quá dài, tối đa 255 ký tự.',
            'channels.required' => 'Bạn chưa chọn kênh bán hàng.',
            'category_id.required' => 'Bạn chưa chọn danh mục.',
            'manufacturer_id.required' => 'Bạn chưa chọn nhà sản xuất.',
            'code.alpha_num' => 'Mã sản phẩm phải là số hoặc chữ.',
            'code.max' => 'Mã sản phẩm quá dài, tối đa 255 ký tự.',
            'imageBase64.required' => 'Bạn chưa chọn ảnh sản phẩm.',
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

        $filename = '';
        $file = request('imageBase64')['file'];
        $filename = md5(uniqid() . '_' . time()) . '_' . request('imageBase64')['name'];
        $img = Image::make($file);
        $img->save(storage_path('app/public/' . $filename));


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
            'name' => trim(request('name')),
            'status' => filter_var(request('status'), FILTER_VALIDATE_BOOLEAN),
            'image' => url('/') . '/storage/' . $filename,
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
        ], [
            'name.unique' => 'Tên sản phẩm đã tồn tại.',
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

        $product->setChannels($channels);

        event(new ProductUpserted($product));

        if ($product->needToOffOnMagento()) {
            $product->setOffOnMagento();
        }

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

    public function listing()
    {
        $sorting = request('sorting', 'id');

        $direction = request('direction', 'desc');

        $page = request('page', 1);

        $limit = request('limit', 25);

        $builder = Product::where(function ($query) {
            if (! empty(request('q'))) {
                $query->where('id', 'like', '%'.request('q').'%')
                    ->orWhere('code', 'like', '%'.request('q').'%')
                    ->orWhere('sku', 'like', '%'.request('q').'%')
                    ->orWhere('name', 'like', '%'.request('q').'%');
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
