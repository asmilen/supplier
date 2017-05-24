<?php

namespace App\Models;

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
}
