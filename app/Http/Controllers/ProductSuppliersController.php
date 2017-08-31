<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\Manufacturer;
use App\Models\ProductSupplier;
use App\Jobs\UpdateAllProductPricesToMagento;
use Carbon\Carbon;
use Sentinel;

class ProductSuppliersController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        $manufacturers = Manufacturer::all();

        $suppliers = Supplier::all();

        return view('product-suppliers.index', compact('categories', 'manufacturers', 'suppliers'));
    }

    public function getDatatables()
    {
        return ProductSupplier::getDatatables();
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

        $exists = ProductSupplier::where('product_id', request('product_id'))
            ->where('supplier_id', request('supplier_id'))
            ->first();

        if ($exists) {
            return response()->json([
                'error' => 'Sản phẩm theo NCC này đã tồn tại, vui lòng tìm kiếm và sửa.'
            ], 422);
        }

        $productSupplier = ProductSupplier::forceCreate([
            'product_id' => request('product_id'),
            'supplier_id' => request('supplier_id'),
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

        $affected = ProductSupplier::whereIn('id',request('productSupplierIds'))
            ->update([
                'from_date' => request('from_date'),
                'to_date' => request('to_date'),
                'updated_by' => Sentinel::getUser()->id,
                'updated_at' => Carbon::now(),
            ]);

        return $affected;
    }
}
