<?php

namespace App\Models;

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
}
