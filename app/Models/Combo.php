<?php

namespace App\Models;

use Datatables;
use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
    public function products()
    {
        return $this->belongsToMany(Product::class,'product_combos','combo_id','product_id')->withPivot( 'quantity');
    }

    public static function getDatatables()
    {
        $model = static::select([
            'id', 'name', 'code', 'status'
        ])->with('products');

        return Datatables::eloquent($model)
            ->filter(function ($query) {

            })
            ->editColumn('quantity', function ($model) {
                return count($model->products) ? count($model->products) : 0;
            })
            ->addColumn('status', 'productCombo.datatables.status')
            ->addColumn('action', 'productCombo.datatables.action')
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}
