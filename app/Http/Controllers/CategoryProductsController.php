<?php

namespace App\Http\Controllers;

use Intervention\Image\Facades\Image as Image;
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
        ], [
            'name.unique' => 'Tên sản phẩm đã tồn tại.',
            'name.required' => 'Bạn chưa nhập tên sản phẩm.',
            'name.max' => 'Tên sản phẩm quá dài, tối đa 255 ký tự.',
            'channels.required' => 'Bạn chưa chọn kênh bán hàng.',
            'manufacturer_id.required' => 'Bạn chưa chọn nhà sản xuất.',
        ])->after(function ($validator) use ($channels) {
            if (empty($channels)) {
                $validator->errors()->add('channels', 'Bạn chưa chọn kênh bán hàng.');
            }

            if (!empty(request('imageBase64'))) {
                $image = request('imageBase64');

                if ($image['filesize'] > 2000000){
                    $validator->errors()->add('image', 'Ảnh không được vượt quá 2MB');
                }

                if (($image['filetype'] != 'image/png') && ($image['filetype'] != 'image/jpeg')){
                    $validator->errors()->add('image', 'Định dạng ảnh phải là jpg hoặc png');
                }
            }
        })->validate();

        if (!empty(request('imageBase64'))) {
            $file = request('imageBase64')['base64'];
            $filename = md5(uniqid() . '_' . time()) . '_' . request('imageBase64')['filename'];
            $img = Image::make($file);
            $img->save(storage_path('app/public/' . $filename));
        }

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

        $product->generateSku()->setChannels($channels);



        event(new ProductUpserted($product));

        return $product;
    }
}
