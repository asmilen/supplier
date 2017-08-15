<?php

namespace App\Models;

use DB;
use Datatables;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    public function products()
    {
        return $this->belongsToMany(Product::class,'bundle_product','id_bundle','id_product')->withPivot('is_default', 'quantity');
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class,'bundle_category','id_bundle','category')->withPivot('name', 'isRequire');
    }

    public static function getDatatables()
    {
        $model = static::select([
            'id', 'name', 'price', 'region_id', 'label'
        ]);

        return Datatables::eloquent($model)
            ->editColumn('region_id', 'bundles.datatables.region')
            ->editColumn('label', 'bundles.datatables.label')
            ->editColumn('price', function ($bundle) {
                return number_format($bundle->price);
            })
            ->addColumn('action', 'bundles.datatables.action')
            ->rawColumns(['action'])
            ->make(true);
    }

    public static function getActiveList()
    {
        return static::pluck('name', 'id')->all();
    }

    public function listProductBySuppliers($supplierIds, $productIds, $regionId)
    {
        $products = Product::select([
            'products.id', 'products.name', 'products.sku'
            , DB::raw('MIN(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * (1 + 0.01 * IFNULL(margin_region_category.margin,5))/1000) * 1000)) as price')
        ])
            ->join('product_supplier', function ($q) use ($supplierIds) {
                $q->on('product_supplier.product_id', '=', 'products.id')
                    ->whereIn('product_supplier.supplier_id', $supplierIds)
                    ->where('product_supplier.state', '=', 1);
            })
            ->leftJoin('margin_region_category', function ($q) use ($regionId) {
                $q->on('margin_region_category.category_id', '=', 'products.category_id')
                    ->where('margin_region_category.region_id', $regionId);
            })->whereNotIn('products.id',$productIds)
            ->groupBy('products.id', 'products.name', 'products.sku');

        return Datatables::of($products)
            ->addColumn('check', function ($product) {
                return '<input  type="checkbox" value="' . $product->id . '" class="checkbox"/>';
            })
            ->addColumn('quantity', function () {
                return '<input  class="qty"  type="number" min = 0 value="1"/>';
            })
            ->addColumn('default', function ($product) {
                return '<input  class="radio" type="radio"  value="' . $product->id . '" name="default"/>';
            })
            ->rawColumns(['check', 'quantity', 'default'])
            ->make(true);
    }
}
