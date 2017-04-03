<?php

namespace App\Models;

use Datatables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public static function getDatatables()
    {
        $model = static::select([
                'id', 'category_id', 'manufacturer_id', 'name', 'code', 'source_url', 'sku', 'status',
            ])->with('category', 'manufacturer');

        return Datatables::eloquent($model)
            ->editColumn('category_id', function ($model) {
                return $model->category ? $model->category->name : '';
            })
            ->editColumn('manufacturer_id', function ($model) {
                return $model->manufacturer ? $model->manufacturer->name : '';
            })
            ->editColumn('status', 'products.datatables.status')
            ->addColumn('action', 'products.datatables.action')
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
}
