<?php

namespace App\Http\Controllers\GoogleSpreadsheet;

use Sentinel;
use App\Models\User;
use App\Models\Product;
use App\Events\ProductUpserted;
use App\Http\Controllers\Controller;

class CreateProducts extends Controller
{
    public function __invoke()
    {
        $user = User::where('email', request('user_email'))->firstOrFail();

        Sentinel::login($user);

        $results = [];

        foreach (request('products', []) as $productData) {
            try {
                $product = $this->createProductFromGoogleSheetData($productData);

                array_push($results, [$product->sku, 'Nhập thành công.']);
            } catch (\Exception $e) {
                array_push($results, ['', 'Lỗi: '.$e->getMessage()]);
            }
        }

        return response()->json($results);
    }

    protected function createProductFromGoogleSheetData($productData)
    {
        $product = Product::where('name', $productData['name'])->first();

        if ($product) {
            return $product;
        }

        $product = Product::forceCreate([
            'category_id' => $productData['category_id'],
            'manufacturer_id' => $productData['manufacturer_id'],
            'color_id' => isset($productData['color_id']) ? $productData['color_id'] : 0,
            'name' => $productData['name'],
            'source_url' => isset($productData['source_url']) ? $productData['source_url'] : null,
            'description' => isset($productData['description']) ? $productData['description'] : null,
            'status' => 0,
        ]);

        event(new ProductUpserted($product));

        return $product;
    }
}
