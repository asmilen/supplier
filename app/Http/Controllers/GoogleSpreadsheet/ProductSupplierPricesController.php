<?php

namespace App\Http\Controllers\GoogleSpreadsheet;

use Sentinel;
use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\ProductSupplier;
use App\Http\Controllers\Controller;

class ProductSupplierPricesController extends Controller
{
    public function update()
    {
        try {
            $user = User::where('email', request('user_email'))->firstOrFail();

            Sentinel::login($user);

            $supplier = Supplier::where('name', request('supplier_name'))->firstOrFail();

            foreach (request('products', []) as $productData) {
                $product = Product::findOrFail($productData['id']);

                $productSupplier = ProductSupplier::where('supplier_id', $supplier->id)
                    ->where('product_id', $product->id)
                    ->first();

                if (! $productSupplier) {
                    $productSupplier = ProductSupplier::forceCreate([
                        'supplier_id' => $supplier->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                    ]);
                }

                $productSupplier->forceFill([
                    'import_price' => $productData['import_price'] ?: 0,
                    'min_quantity' => $productData['min_quantity'] ?: 0,
                    'state' => 1,
                ])->save();
            }

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
