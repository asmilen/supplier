<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\ProductSupplier;
use App\Models\Supplier;
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
                array_push($results, [Carbon::now()->format('d/m/Y H:i:s'), 'Lỗi: '.$e->getMessage()]);
            }
        }

        return response()->json($results);
    }

    protected function updateProductFromGoogleSheetData($productData)
    {
        $product = Product::where('sku', $productData['product_sku'])->firstOrFail();

        $supplier = Supplier::findOrFail($productData['supplier_id']);

        $productSupplier = ProductSupplier::where('supplier_id', $productData['supplier_id'])
            ->where('product_id', $product->id)
            ->first();

        if (!$productSupplier) {
            $productSupplier = ProductSupplier::forceCreate([
                'supplier_id'=> $supplier->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'created_by' => Sentinel::getUser()->id,
            ]);
        }

        $productSupplier->forceFill([
            'status' => $productData['supplier_priority_status'],
            'import_price' => $productData['price'] ? $productData['price'] : 0,
            'vat' => $productData['vat'] ? $productData['vat'] : 0,
            'price_recommend' => $productData['recommend_price'] ? $productData['recommend_price'] : 0,
            'description' => $productData['description'],
            'updated_by' => Sentinel::getUser()->id,
        ])->save();

        return $productSupplier;
    }
}
