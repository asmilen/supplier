<?php

namespace App\Http\Controllers\API;

use DB;
use Sentinel;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\ProductSupplier;
use App\Http\Controllers\Controller;
use Excel;

class ProductSuppliersController extends Controller
{
    public function updatePriceFromGoolgeSheet()
    {
        $results = [];

        foreach (request('form_data') as $data) {
            try {
                $productSupplier = $this->updateProductSupplierFromGoogleSheetData($data);

                array_push($results, [$productSupplier->updated_at->format('d/m/Y H:i:s'), 'Nhập thành công.']);
            } catch (\Exception $e) {
                array_push($results, [Carbon::now()->format('d/m/Y H:i:s'), 'Lỗi: '.$e->getMessage()]);
            }
        }

        return response()->json($results);
    }

    protected function updateProductSupplierFromGoogleSheetData($productData)
    {
        $product = Product::where('sku', $productData['product_sku'])->firstOrFail();

        $supplier = Supplier::findOrFail($productData['supplier_id']);

        $productSupplier = ProductSupplier::where('supplier_id', $supplier->id)
            ->where('product_id', $product->id)
            ->first();

        if (! $productSupplier) {
            $productSupplier = ProductSupplier::forceCreate([
                'supplier_id' => $supplier->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'created_by' => Sentinel::getUser()->id,
            ]);
        }

        $productSupplier->forceFill([
            'status' => $productData['supplier_priority_status'],
            'state' => $productData['state'],
            'import_price' => $productData['price'] ? $productData['price'] : 0,
            'vat' => $productData['vat'] ? $productData['vat'] : 0,
            'price_recommend' => $productData['recommend_price'] ? $productData['recommend_price'] : 0,
            'description' => $productData['description'],
            'updated_by' => Sentinel::getUser()->id,
        ])->save();

        return $productSupplier;
    }

    public function index()
    {
        $sorting = request('sorting', 'name');

        $direction = request('direction', 'asc');

        $page = request('page', 1);

        $limit = request('limit', 10);

        $builder = $this->getProductsListBuilder();

        $totalItems = $builder->count();

        $productSuppliers = $builder
            ->orderBy($sorting, $direction)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return response()->json([
            'data' => $productSuppliers,
            'total_items' => $totalItems,
        ]);
    }

    public function getIds()
    {
        $builder = $this->getProductsListBuilder();

        $productSuppliers = $builder->pluck('id');

        return response()->json([
            'data' => $productSuppliers,
        ]);
    }

    protected function getProductsListBuilder()
    {
        $builder = ProductSupplier::with('product', 'product.category', 'product.manufacturer', 'supplier', 'creater', 'updater')
            ->canManage();

        $builder->whereHas('product', function ($query) {
            $query->where('status', true);
        });

        $builder->whereHas('supplier', function ($query) {
            $query->where('status', true);
        });

        if (! empty(request('category_id'))) {
            $builder->whereHas('product', function ($query) {
                $query->where('category_id', request('category_id'));
            });
        }

        if (! empty(request('manufacturer_id'))) {
            $builder->whereHas('product', function ($query) {
                $query->where('manufacturer_id', request('manufacturer_id'));
            });
        }

        if (! empty(request('supplier_id'))) {
            $builder->where('supplier_id', request('supplier_id'));
        }else{
            $builder->leftJoin('suppliers', 'product_supplier.supplier_id', 'suppliers.id')
                ->where('suppliers.status', 1);
        }

        if (! empty(request('q'))) {
            $builder->whereHas('product', function ($query) {
                $query->where('name', 'like', '%'.request('q').'%')
                    ->orWhere('sku', 'like', '%'.request('q').'%');
            });
        }

        if (request('state') != '') {
            $builder->where('state', request('state'));
        }

        return $builder;
    }

    public function exportExcel()
    {
        $builder = $this->getProductsListBuilder();

        $productSuppliers = $builder->get();

        $productSuppliers->transform(function ($item,$key){
            return [
                'id' => $item->id,
                'Danh mục' => $item->product->category ? $item->product->category->name : '',
                'Nhà Sản xuất' => $item->product->manufacturer ? $item->product->manufacturer->name : '',
                'sku' => $item->product->sku,
                'Tên' => $item->product->name,
                'Nhà cung cấp' => $item->supplier->name,
                'Giá nhập' => $item->import_price,
                'Hiệu lực từ' => $item->from_date,
                'Hiệu lực đến' => $item->to_date,
                'Số lượng tối thiểu' => $item->min_quantity,
                'Giá bán khuyến nghị' => $item->recommend_price,
                'Tình trạng' => config('teko.product.state')[$item->state],
                'Cập nhật lần cuối' => $item->updated_at,
            ];
        });

        Excel::create('supplier_product', function ($excel) use ($productSuppliers) {
            $excel->sheet('Sheet 232', function ($sheet) use ($productSuppliers) {
                $sheet->fromArray($productSuppliers);
            });
        })->store('xlsx', 'exports');

        return [
            'success' => true,
            'path' => 'http://' . request()->getHttpHost() . '/exports/supplier_product.xlsx'
        ];
    }
}
