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
        $suppliers = Supplier::all();

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

        $paginate = request('paginate') ? request('paginate') : 10;
        $listProduct = ProductSupplier::where('to_date','<',Carbon::now())
                ->join('suppliers','suppliers.id','=','product_supplier.supplier_id')
                ->whereIn('supplier_id', $supplierIds)
                ->where('product_supplier.state','=',1)
                ->select('product_supplier.name as product_name','product_supplier.updated_at','product_supplier.to_date','suppliers.name as supplier_name',DB::raw('TIMESTAMPDIFF(DAY,product_supplier.to_date,DATE_ADD(NOW(),INTERVAL 1 DAY)) as out_dated_time'))
                ->paginate($paginate);

        $maxTime = DB::table('product_supplier')
            ->where('to_date','<',Carbon::now())
            ->join('suppliers','suppliers.id','=','product_supplier.supplier_id')
            ->whereIn('supplier_id', $supplierIds)
            ->where('product_supplier.state','=',1)
            ->max(DB::raw('TIMESTAMPDIFF(DAY,product_supplier.to_date,DATE_ADD(NOW(),INTERVAL 1 DAY))'));

        $avgTime = ProductSupplier::where('to_date','<',Carbon::now())
            ->join('suppliers','suppliers.id','=','product_supplier.supplier_id')
            ->whereIn('supplier_id', $supplierIds)
            ->where('product_supplier.state','=',1)
            ->avg(DB::raw('TIMESTAMPDIFF(DAY,product_supplier.to_date,DATE_ADD(NOW(),INTERVAL 1 DAY))'));

        return view('reports.import_price', compact('suppliers','countProduct','listProduct','maxTime','avgTime'));
    }
}
