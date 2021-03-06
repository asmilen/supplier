<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Product;
use App\Models\Category;
use App\Events\ProductUpserted;

class CategoryProductsController extends Controller
{
    public function store(Category $category)
    {
        $channels = array_keys(array_filter(request('channels', []), function ($value, $key) {
            return $value;
        }, ARRAY_FILTER_USE_BOTH));

        Validator::make(request()->all(), [
            'channels' => 'required',
            'manufacturer_id' => 'required',
            'name' => 'required|max:255|unique:products',
            'image_base64' => 'nullable|image_base64'
        ], [
            'name.unique' => 'Tên sản phẩm đã tồn tại.',
            'name.required' => 'Bạn chưa nhập tên sản phẩm.',
            'name.max' => 'Tên sản phẩm quá dài, tối đa 255 ký tự.',
            'channels.required' => 'Bạn chưa chọn kênh bán hàng.',
            'manufacturer_id.required' => 'Bạn chưa chọn nhà sản xuất.',
            'image_base64.image_base64' => 'Ảnh phải có định dạnh jpg/png và kích thước không quá 2MB',
        ])->after(function ($validator) use ($channels) {
            if (empty($channels)) {
                $validator->errors()->add('channels', 'Bạn chưa chọn kênh bán hàng.');
            }
        })->validate();

        $product = Product::forceCreate([
            'category_id' => $category->id,
            'manufacturer_id' => request('manufacturer_id'),
            'color_id' => request('color_id', 0),
            'name' => trim(request('name')),
            'source_url' => trim(request('source_url')),
            'description' => request('description'),
            'status' => !! request('status'),
            'image' => isset($filename) ? url('/') . '/storage/' . $filename : '',
        ]);

        $product
            ->generateSku()
            ->setChannels($channels)
            ->setFeaturedImage(request('image_base64'));

        event(new ProductUpserted($product));

        return $product;
    }
}
