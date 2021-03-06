<?php

namespace App\Http\Controllers\API;

use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        $productIds = \request('product_ids');

        $suppliers = Supplier::select('suppliers.id','suppliers.name','suppliers.sup_type','product_supplier.import_price','product_supplier.product_id')
                        ->join('product_supplier', function ($q) use ($productIds) {
                            $q->on('product_supplier.supplier_id', '=', 'suppliers.id')
                                ->whereIn('product_supplier.product_id', $productIds)
                                ->where('product_supplier.state', '=', 1);
                        })
                        ->where('product_supplier.region_id', $region->region_id)
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

    public function updatePriceValidTimeFromGoolgeSheet()
    {
        $results = [];
        foreach (request('form_data') as $data) {
            try {
                $productSupplier = $this->updateSupplierFromGoogleSheetData($data);

                array_push($results, ['Nhập thành công.']);
            } catch (\Exception $e) {
                array_push($results, [Carbon::now()->format('d/m/Y H:i:s'), 'Lỗi: '.$e->getMessage()]);
            }
        }
        return response()->json($results);
    }

    public function updateSupplierFromGoogleSheetData($data)
    {
        $supplier = Supplier::findOrFail($data['supplier_id']);
        switch ($data['price_valid_time'])
        {
            case 'monthly':
                $priceValidTime = 30 * 24;
                break;
            case 'weekly':
                $priceValidTime = 7 * 24;
                break;
            case 'daily':
                $priceValidTime = 1 * 24;
                break;
            default:
                $priceValidTime = 10 * 24;
        }

        $supplier->forceFill([
            'price_active_time' => $priceValidTime,
        ])->save();

        return $supplier;
    }

    public function search()
    {
        $page = request('page', 1);

        $limit = request('limit', 10);

        $offset = ($page - 1) * $limit;

        $builder = Supplier::active()
            ->where(function ($query) {
                $q = request('q');

                $query->where('id', $q)
                    ->orWhere('name', 'like', '%'.$q.'%');
            });

        $totalItems = $builder->count();

        $suppliers = $builder->skip($offset)
            ->take($limit)
            ->get();

        return response()->json([
            'data' => $suppliers,
            'total_items' => $totalItems,
        ]);
    }

    public function getSuppliersByRegionId()
    {
        $provinceIds = Province::where(function ($query) {
            if (request()->has('region_id'))
                $query->where('region_id',request('region_id'));
        })
            ->get()
            ->pluck('id');

        $suppliers = Supplier::join('supplier_supported_province', 'suppliers.id','=','supplier_supported_province.supplier_id')
            ->whereIn('supplier_supported_province.province_id', $provinceIds)
            ->select('suppliers.id','suppliers.name')
            ->get();

        return $suppliers;
    }
}
