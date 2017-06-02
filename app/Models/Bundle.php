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

    public function listProductBySuppliers($supplierIds)
    {
        return Product::select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`"))
            ->join('product_supplier', function ($q) use ($supplierIds) {
                $q->on('product_supplier.product_id', '=', 'products.id')
                    ->whereIn('product_supplier.supplier_id', $supplierIds)
                    ->where('product_supplier.state', '=', 1);
            })->get();
    }


}
