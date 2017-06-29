<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\ProductSupplier;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Support\Facades\Log;

class ProductSuppliersController extends Controller
{
    public function updatePriceFromGoolgeSheet()
    {
        $results = [];
        foreach (request('form_data') as $data) {
            try {
                $productSupplier = $this->updateProductFromGoogleSheetData($data);
                array_push($results, [$productSupplier->updated_at->format('d/m/Y H:i:s'), 'Nhập thành công.']);
            } catch (\Exception $e) {
                array_push($results, ['', 'Lỗi: '.$e->getMessage()]);
                Log::info($e);
            }
        }

        return response()->json($results);
    }

    protected function updateProductFromGoogleSheetData($productData)
    {

        $product = Product::where('sku', $productData['product_sku'])->first();

        if (!$product) {
            throw new \Exception('Mã sản phẩm không tồn tại.');
        }

        $productSupplier = ProductSupplier::where('supplier_id', $productData['supplier_id'])
            ->where('product_id', $product->id)
            ->first();

        if ($productSupplier) {
            $productSupplier->forceFill([
                'status' => $productData['supplier_priority_status'],
                'import_price' => $productData['price'] ? $productData['price'] : 0,
                'vat' => $productData['vat'] ? $productData['vat'] : 0,
                'price_recommend' => $productData['recommend_price'] ? $productData['recommend_price'] : 0,
                'description' => $productData['description'],
                'updated_by' => Sentinel::getUser()->id,
            ])->save();
        }
        else
        {
            $productSupplier = ProductSupplier::forceCreate([
                'supplier_id'=> $productData['supplier_id'],
                'product_id' => $product->id,
                'name' => $product->name,
                'status' => $productData['supplier_priority_status'],
                'import_price' => $productData['price'] ? $productData['price'] : 0,
                'vat' => $productData['vat'] ? $productData['vat'] : 0,
                'price_recommend' => $productData['recommend_price'] ? $productData['recommend_price'] : 0,
                'description' => $productData['description'],
                'created_by' => Sentinel::getUser()->id,
                'updated_by' => Sentinel::getUser()->id,
            ]);
        }

        return $productSupplier;
    }
}
