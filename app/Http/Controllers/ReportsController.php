<?php

namespace App\Http\Controllers;

use App\Models\ProductSupplier;
use App\Models\Province;
use App\Models\Supplier;
use App\Models\SupplierSupportedProvince;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    //
    public function importPrice()
    {


        $provinceIds = Province::where(function ($query) {
                if (request()->has('region_id'))
                    $query->where('region_id',request('region_id'));
            })
            ->get()
            ->pluck('id');

        $supplierIds = SupplierSupportedProvince::whereIn('province_id', $provinceIds)
            ->where(function ($query) {
                if (request()->has('supplier_id'))
                    $query->where('supplier_id',request('supplier_id'));
            })
            ->get()
            ->pluck('supplier_id');

        $suppliers = Supplier::whereIn('id', $supplierIds)
                        ->get();

        $paginate = request('paginate') ? request('paginate') : 10;

        $listProduct = ProductSupplier::whereRaw('DATEDIFF(CURRENT_DATE,DATE(product_supplier.to_date)) > 0')
                ->join('suppliers','suppliers.id','=','product_supplier.supplier_id')
                ->whereIn('supplier_id', $supplierIds)
                ->where('product_supplier.state','=',1)
                ->select('product_supplier.name as product_name','product_supplier.updated_at','product_supplier.to_date','suppliers.name as supplier_name',DB::raw('DATEDIFF(CURRENT_DATE,DATE(product_supplier.to_date)) as out_dated_time'))
                ->paginate($paginate);

        $maxTime = ProductSupplier::whereRaw('DATEDIFF(CURRENT_DATE,DATE(product_supplier.to_date)) > 0')
            ->join('suppliers','suppliers.id','=','product_supplier.supplier_id')
            ->whereIn('supplier_id', $supplierIds)
            ->where('product_supplier.state','=',1)
            ->max(DB::raw('DATEDIFF(CURRENT_DATE,DATE(product_supplier.to_date))'));

        $avgTime = ProductSupplier::whereRaw('DATEDIFF(CURRENT_DATE,DATE(product_supplier.to_date)) > 0')
            ->join('suppliers','suppliers.id','=','product_supplier.supplier_id')
            ->whereIn('supplier_id', $supplierIds)
            ->where('product_supplier.state','=',1)
            ->avg(DB::raw('DATEDIFF(CURRENT_DATE,DATE(product_supplier.to_date))'));

        return view('reports.import_price', compact('suppliers','countProduct','listProduct','maxTime','avgTime'));
    }
}
