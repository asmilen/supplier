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
        return $this->belongsTo(Bundle::class,'id_bundle');
    }

    public function bundleCategory()
    {
        return $this->belongsTo(BundleCategory::class,'id_bundleCategory');
    }

    public function product()
    {
        return $this->belongsTo(Product::class,'id_product');
    }

    public static function getDatatables()
    {
        $model = static::select([
            'id','id_product', 'id_bundleCategory','id_bundle','is_default','quantity'
        ])->with('bundle','bundleCategory', 'product');

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
            ->rawColumns(['is_default','action'])
            ->make(true);
    }

    public static function getActiveList()
    {
        return static::pluck('name', 'id')->all();
    }

    public function getProduct($supplierIds, $regionId)
    {
        $product = Product::select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`, `product_supplier`.`image` as `source_url`,`products`.`category_id`, `product_supplier`.`state`"))
            ->join('product_supplier', function ($q) use ($supplierIds) {
                $q->leftJoin('product_supplier.product_id', '=', 'products.id')
                    ->whereIn('product_supplier.supplier_id', $supplierIds)
                    ->where('product_supplier.state', '=', 1);
            })
            ->findOrFail($this->id_product);

        $margin = MarginRegionCategory::where('category_id', $product->category_id)
            ->where('region_id', $regionId)->first();

        $productMargin = $margin ? 1 + 0.01 * $margin->margin : 1.05;

        $product->best_price = ProductSupplier::where('product_id', $product->id)
            ->whereIn('product_supplier.supplier_id', $supplierIds)
            ->min(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * ' . $productMargin . '/1000) * 1000))'));

        $product->import_price = ProductSupplier::where('product_id', $product->id)
            ->whereIn('product_supplier.supplier_id', $supplierIds)
            ->min(DB::raw('(ceil(product_supplier.import_price/1000) * 1000)'));

        $product->quantity = $this->quantity;

        $product->isDefault = $this->is_default;

        return $product;
    }
}
