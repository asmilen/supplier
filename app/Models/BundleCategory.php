<?php

namespace App\Models;

use DB;
use Datatables;
use Illuminate\Database\Eloquent\Model;

class BundleCategory extends Model
{
    protected $table = "bundle_category";

    public function bundle()
    {
        return $this->belongsTo(Bundle::class,'id_bundle');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class,'bundle_product','id_bundleCategory','id_product')->withPivot('is_default', 'quantity','id_bundle');
    }

    public static function getDatatables()
    {
        $model = static::select([
            'id', 'name','id_bundle','isRequired'
        ])->with('bundle');

        return Datatables::eloquent($model)
            ->editColumn('price', function ($bundle) {
                return number_format($bundle->price);
            })
            ->editColumn('nameBundle', function ($model) {
                return $model->bundle ? $model->bundle->name : '';
            })
            ->editColumn('totalProduct', function ($model) {
                return count($model->products) ? count($model->products) : 0;
            })
            ->addColumn('action', 'bundleCategories.datatables.action')
            ->rawColumns(['action'])
            ->make(true);
    }

    public static function getActiveList()
    {
        return static::pluck('name', 'id')->all();
    }

    public static function getListByBundleId($bundleId)
    {
        return static::where('id_bundle', $bundleId)->get();
    }

    public function getBundleProducts($supplierIds, $regionId)
    {
        $bundleProducts = BundleProduct::where('id_bundle', $this->id_bundle)
            ->where('id_bundleCategory', $this->id)
            ->get();

        return $bundleProducts->map(function ($bundleProduct) use ($supplierIds, $regionId) {
            return $bundleProduct->getProduct($supplierIds, $regionId);
        });
    }

    public function listProductBySuppliersNotExist($supplierIds, $productIds, $regionId)
    {
        return Product::select([
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
            ->groupBy('products.id', 'products.name', 'products.code',
                'products.sku', 'products.source_url', 'products.best_price',
                'products.category_id')->get();
    }

    public function listProducts($supplierIds, $idBundleCategory, $regionId)
    {
        return Product::select([
            'products.id', 'products.name', 'products.sku', 'bundle_product.is_default as is_default', 'bundle_product.quantity as quantity', 'bundle_product.id_bundle as id_bundle'
            , DB::raw('MIN(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * (1 + 0.01 * IFNULL(margin_region_category.margin,5))/1000) * 1000)) as price')
        ])
            ->join('bundle_product', function ($q) use ($idBundleCategory) {
                $q->on('bundle_product.id_product', '=', 'products.id')
                    ->where('bundle_product.id_bundleCategory', '=', $idBundleCategory);
            })
            ->join('product_supplier', function ($q) use ($supplierIds) {
                $q->on('product_supplier.product_id', '=', 'products.id')
                    ->whereIn('product_supplier.supplier_id', $supplierIds)
                    ->where('product_supplier.state', '=', 1);
            })
            ->leftJoin('margin_region_category', function ($q) use ($regionId) {
                $q->on('margin_region_category.category_id', '=', 'products.category_id')
                    ->where('margin_region_category.region_id', $regionId);
            })
            ->groupBy('products.id', 'products.name', 'products.code',
                'products.sku', 'products.source_url', 'products.best_price',
                'products.category_id')->get();
    }
}
