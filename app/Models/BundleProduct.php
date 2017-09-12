<?php

namespace App\Models;

use DB;
use Datatables;
use Illuminate\Database\Eloquent\Model;

class BundleProduct extends Model
{
    protected $table = "bundle_product";

    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'id_bundle');
    }

    public function bundleCategory()
    {
        return $this->belongsTo(BundleCategory::class, 'id_bundleCategory');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public static function getDatatables()
    {
        $model = static::select([
            'id', 'id_product', 'id_bundleCategory', 'id_bundle', 'is_default', 'quantity'
        ])->with('bundle', 'bundleCategory', 'product');

        return Datatables::eloquent($model)
            ->addColumn('nameProduct', function ($model) {
                return $model->product ? $model->product->name : '';
            })
            ->editColumn('nameCategory', function ($model) {
                return $model->bundleCategory ? $model->bundleCategory->name : '';
            })
            ->editColumn('nameBundle', function ($model) {
                return $model->bundle ? $model->bundle->name : '';
            })
            ->editColumn('is_default', 'bundleProducts.datatables.status')
            ->addColumn('action', 'bundleProducts.datatables.action')
            ->rawColumns(['is_default', 'action'])
            ->make(true);
    }

    public static function getActiveList()
    {
        return static::pluck('name', 'id')->all();
    }

    public function getProduct($supplierIds, $regionId, $provinceId)
    {
        $product = Product::select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`, `product_supplier`.`image` as `source_url`,`products`.`category_id`, if(sum(`product_supplier`.`state`) > 0, 1, 0) as 'state', `products`.`status`,`products`.`updated_at` as 'last_update_info', max(`product_supplier`.`updated_at`) as 'last_update_price'"))
            ->join('product_supplier', function ($q) use ($supplierIds) {
                $q->on('product_supplier.product_id', '=', 'products.id')
                    ->whereIn('product_supplier.supplier_id', $supplierIds);
            })
            ->where('products.channel', 'like', '%' . 2 . '%')
            ->findOrFail($this->id_product);

        $margin = MarginRegionCategory::where('category_id', $product->category_id)
            ->where('region_id', $regionId)
            ->first();

        $productMarginFee = $this->getProductMarginFee($product, $supplierIds, $regionId, $provinceId);

        $marginValue = $productMarginFee - ($margin ? $margin->margin * 0.01 : 0.05);

        $product->best_price = ProductSupplier::where('product_id', $product->id)
            ->whereIn('product_supplier.supplier_id', $supplierIds)
            ->where('state', 1)
            ->min(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * ' . $productMarginFee . '/1000) * 1000))'));

        $product->import_price = ProductSupplier::where('product_id', $product->id)
            ->whereIn('product_supplier.supplier_id', $supplierIds)
            ->where('state', 1)
            ->min(DB::raw('(ceil(product_supplier.import_price * (' . $marginValue . ')/1000) * 1000)'));

        $product->import_price_w_margin = ProductSupplier::where('product_id', $product->id)
            ->whereIn('product_supplier.supplier_id', $supplierIds)
            ->where('state', 1)
            ->min(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * ' . $productMarginFee . '/1000) * 1000))'));

        $product->recommended_price = ProductSupplier::where('product_id', $product->id)
            ->whereIn('product_supplier.supplier_id', $supplierIds)
            ->where('price_recommend', '!=', 0)
            ->where('state', 1)
            ->min(DB::raw('(ceil(product_supplier.price_recommend/1000) * 1000)'));

        $product->quantity = $this->quantity;

        $product->isDefault = $this->is_default;

        $product->state = config('teko.product.state')[$product->state];

        return $product;
    }

    protected function getProductMarginFee($product, $supplierIds, $regionId, $provinceCode)
    {
        $margin = MarginRegionCategory::where('category_id', $product->category_id)
            ->where('region_id', $regionId)
            ->first();
        $marginValue = ($margin ? 1 + 0.01 * $margin->margin : 1.05);
        $province = Province::where('region_id', $regionId)
            ->where('code', $provinceCode)
            ->first();
        $provinceId = $province ? $province->id : 0;

        $provinceFee_index = TransportFee::where('province_id', $provinceId)->first();

        $feeValue = ($provinceFee_index ? $provinceFee_index->percent_fee : 0) * 0.01;

        $minPrice = ProductSupplier::where('product_id', $product->id)
            ->whereIn('product_supplier.supplier_id', $supplierIds)
            ->leftJoin('supplier_supported_province', 'product_supplier.supplier_id', '=', 'supplier_supported_province.supplier_id')
            ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'supplier_supported_province.province_id')
            ->where('product_supplier.state', '=', 1)
            ->orderBy(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, product_supplier.import_price * (' .
                $marginValue . '+' . $feeValue . '+' . '(case when supplier_supported_province.province_id = ' . $provinceId . ' then 0 else if(transport_fees.percent_fee is null, 0,transport_fees.percent_fee) end ))))'))
            ->orderBy('transport_fees.percent_fee')
            ->first();

        $provinceFeeMin = SupplierSupportedProvince::with('transportFee')
            ->where('supplier_id', $minPrice ? $minPrice->supplier->id : 0)
            ->leftJoin('transport_fees', 'transport_fees.province_id', 'supplier_supported_province.province_id')
            ->orderBy('transport_fees.percent_fee')
            ->first();

        $supportProvince = SupplierSupportedProvince::where('supplier_id', $provinceFeeMin ? $provinceFeeMin->supplier_id : 0)
            ->leftJoin('provinces', 'provinces.id', 'supplier_supported_province.province_id')
            ->where('provinces.region_id', $regionId)
            ->pluck('province_id');

        $provinceFee = TransportFee::where('province_id', $provinceFeeMin ? $provinceFeeMin->province_id : 0)->first();

        if (in_array($provinceId, $supportProvince ? $supportProvince->toArray() : [])) {
            $productMarginFee = $marginValue + $feeValue;
        } else {
            $productMarginFee = $marginValue + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01 + $feeValue;
        }

        return $productMarginFee;
    }
}
