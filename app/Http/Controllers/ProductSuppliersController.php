<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\ProductSupplier;
use App\Jobs\UpdateAllProductPricesToMagento;

class ProductSuppliersController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        $manufacturers = Manufacturer::all();

//        $suppliers = Supplier::all();
        $suppliers = Supplier::where('status', 1)->get();
//dd($suppliers->count());
        return view('product-suppliers.index', compact('categories', 'manufacturers', 'suppliers'));
    }

    public function store()
    {
        $this->validate(request(), [
            'product_id' => 'required',
            'supplier_id' => 'required',
            'import_price' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
        ]);

        $product = Product::active()->findOrFail(request('product_id'));

        $supplier = Supplier::active()->findOrFail(request('supplier_id'));

        $exists = ProductSupplier::where('product_id', $product->id)
            ->where('supplier_id', $supplier->id)
            ->first();

        if ($exists) {
            return response()->json([
                'error' => 'Sản phẩm theo NCC này đã tồn tại, vui lòng tìm kiếm và sửa.'
            ], 422);
        }

        $productSupplier = ProductSupplier::forceCreate([
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'name' => request('product_name'),
            'import_price' => request('import_price'),
            'from_date' => request('from_date'),
            'to_date' => request('to_date'),
            'min_quantity' => request('min_quantity', 0),
            'price_recommend' => request('price_recommend', 0),
            'state' => 1,
        ]);

        return $productSupplier;
    }

    public function update($id)
    {
        $productSupplier = ProductSupplier::findOrFail($id);

        $product = Product::active()->findOrFail($productSupplier->product_id);

        $supplier = Supplier::active()->findOrFail($productSupplier->supplier_id);

        $this->validate(request(), [
            'import_price' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
            'state' => 'required',
        ]);

        $productSupplier->forceFill([
            'import_price' => request('import_price'),
            'from_date' => request('from_date'),
            'to_date' => request('to_date'),
            'min_quantity' => request('min_quantity', 0),
            'price_recommend' => request('price_recommend', 0),
            'state' => request('state'),
        ])->save();

        return $productSupplier;
    }

    public function updateAllPricesToMagento()
    {
        dispatch(new UpdateAllProductPricesToMagento());
    }

    public function updateValidTime()
    {
        $this->validate(request(), [
            'from_date' => 'required',
            'to_date' => 'required',
        ]);

        $affected = ProductSupplier::whereIn('id', request('productSupplierIds'))
            ->update([
                'from_date' => request('from_date'),
                'to_date' => request('to_date'),
            ]);

        return $affected;
    }
}
