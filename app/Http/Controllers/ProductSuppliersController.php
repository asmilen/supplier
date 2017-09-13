<?php

namespace App\Http\Controllers;

use App\Jobs\OffProductToMagento;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\ProductSupplier;
use App\Jobs\UpdateAllProductPricesToMagento;
use Sentinel;

class ProductSuppliersController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        $manufacturers = Manufacturer::all();

        $suppliers = Supplier::where('status', 1)->get();

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

        if (!in_array(true, request('regions'))) {
            return response()->json([
                'error' => 'Bạn phải chọn ít nhất 1 miền.'
            ],422);
        }

        $product = Product::active()->findOrFail(request('product_id'));

        $supplier = Supplier::active()->findOrFail(request('supplier_id'));

        foreach (request('regions') as $regionId => $flagRegion) {
            if ($flagRegion) {
                $productSupplier = ProductSupplier::where('product_id', $product->id)
                    ->where('supplier_id', $supplier->id)
                    ->where('region_id',$regionId)
                    ->first();

                if (!$productSupplier) {
                    $productSupplier = ProductSupplier::forceCreate([
                        'product_id' => $product->id,
                        'supplier_id' => $supplier->id,
                        'name' => request('product_name'),
                        'region_id' => $regionId,
                    ]);
                }

                $productSupplier->forceFill([
                    'import_price' => request('import_price'),
                    'from_date' => request('from_date'),
                    'to_date' => request('to_date'),
                    'min_quantity' => request('min_quantity', 0),
                    'price_recommend' => request('price_recommend', 0),
                    'state' => 1,
                ])->save();
            }
        }

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

        $user = Sentinel::getUser();
        if (request('state') == 1) {
            dispatch(new OffProductToMagento($productSupplier, 1, $user));
        }elseif(request('state') == 0){
            $suppliers = ProductSupplier::where('product_supplier.product_id', $productSupplier->product_id)
                ->leftJoin('suppliers', 'product_supplier.supplier_id', 'suppliers.id')
                ->where('product_supplier.state', 1)
                ->where('suppliers.status', 1)
                ->where('suppliers.id', '!=', $productSupplier->supplier_id)
                ->count();
            if ($suppliers == 0) {
                dispatch(new OffProductToMagento($productSupplier, 0, $user));
            }
        }

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
