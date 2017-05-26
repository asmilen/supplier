<?php

namespace App\Models;

use Datatables;
use Illuminate\Database\Eloquent\Model;

class Margin extends Model
{
    public static function getDatatables()
    {
        $model = static::select([
            'id', 'label','value'
        ]);

        return Datatables::eloquent($model)
            ->editColumn('value', function ($model) {
                return $model->value . ' %';
            })
            ->addColumn('action', 'margins.datatables.action')
            ->rawColumns(['action'])
            ->make(true);
    }
}
