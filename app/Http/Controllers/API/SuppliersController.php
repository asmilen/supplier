<?php

namespace App\Http\Controllers\API;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Models\Province;
use App\Models\SupplierSupportedProvince;

class SuppliersController extends Controller
{
    public function index()
    {
        return Supplier::all();
    }

    public function getListSupplierByProductId()
    {
        $validator = Validator::make(request()->all(), [
            'region_code' => 'required',
            'product_ids' => 'required|array',
            'product_ids.*' => 'required',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $region = Province::where('code', request('region_code'))->firstOrFail();

        $provinceIds = Province::where('region_id', $region->region_id)->pluck('id');

        $supplierIds = SupplierSupportedProvince::whereIn('province_id', $provinceIds)
            ->get()
            ->pluck('supplier_id');

        $productIds = \request('product_ids');

        $suppliers = Supplier::select('suppliers.id','suppliers.name','product_supplier.import_price','product_supplier.product_id')
                        ->join('product_supplier', function ($q) use ($productIds) {
                            $q->on('product_supplier.supplier_id', '=', 'suppliers.id')
                                ->whereIn('product_supplier.product_id', $productIds);
                        })
                        ->whereIn('suppliers.id', $supplierIds)
                        ->where('suppliers.status',true)
                        ->get();

        $response = [];
        foreach ($suppliers as $supplier)
        {
            $addresses = DB::table('supplier_addresses')
                    ->where('supplier_id',$supplier->id)
                    ->select('id','address','contact_name','contact_phone','contact_email','province_name as province','district_name as district')
                    ->get();
            $supplier->addresses = $addresses;
            $response[$supplier->product_id][] = $supplier;
        }
        return $response;
    }
}
