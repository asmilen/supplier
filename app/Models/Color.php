<?php

namespace App\Models;

use Datatables;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public static function getDatatables()
    {
        $model = static::select([
            'id', 'name',
        ]);

        return Datatables::eloquent($model)
            ->addColumn('action', 'colors.datatables.action')
            ->rawColumns(['action'])
            ->make(true);
    }

    public static function getActiveList()
    {
        return static::pluck('name', 'id')->all();
    }
}
