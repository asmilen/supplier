<?php

namespace App\Models;

use Datatables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manufacturer extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public static function getDatatables()
    {
        $model = static::select([
                'id', 'code', 'name', 'homepage', 'status',
            ]);

        return Datatables::eloquent($model)
            ->editColumn('status', 'manufacturers.datatables.status')
            ->addColumn('action', 'manufacturers.datatables.action')
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public static function getList()
    {
        return static::pluck('name', 'id')->all();
    }
}
