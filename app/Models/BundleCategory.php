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

    public function listProductBySuppliersNotExist($supplierIds,$productIds)
    {
        return Product::select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`"))
            ->join('product_supplier', function ($q) use ($supplierIds) {
                $q->on('product_supplier.product_id', '=', 'products.id')
                    ->whereIn('product_supplier.supplier_id', $supplierIds)
                    ->where('product_supplier.state', '=', 1);
            })
            ->whereNotIn('products.id',$productIds)->get();
    }
}
