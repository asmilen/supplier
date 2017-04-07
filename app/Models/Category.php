<?php

namespace App\Models;

use Datatables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
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

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public static function getDatatables()
    {
        $model = static::select([
                'id', 'code', 'name', 'status',
            ]);

        return Datatables::eloquent($model)
            ->editColumn('status', 'categories.datatables.status')
            ->addColumn('action', 'categories.datatables.action')
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public static function getActiveList()
    {
        return static::active()->pluck('name', 'id')->all();
    }
}
