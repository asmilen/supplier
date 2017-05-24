<?php

namespace App\Models;

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
            'id', 'name','price'
        ]);

        return Datatables::eloquent($model)
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
}
