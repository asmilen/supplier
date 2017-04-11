<?php

namespace App\Models;

use Datatables;
use Illuminate\Database\Eloquent\Model;

class Suppliers extends Model
{

    public static function getDatatables()
    {
        $model = static::select([
                'id', 'category_id', 'manufacturer_id', 'name', 'code', 'source_url', 'sku', 'status',
            ])->with('product');

        return Datatables::eloquent($model)
            ->filter(function ($query) {
                if (request()->has('keyword')) {
                    $query->where(function ($query) {
                        $query->where('name', 'like', '%'.request('keyword').'%')
                            ->orWhere('code', 'like', '%'.request('keyword').'%')
                            ->orWhere('sku', 'like', '%'.request('keyword').'%');
                    });
                }

                if (request()->has('category_id')) {
                    $query->where('category_id', request('category_id'));
                }

                if (request()->has('manufacturer_id')) {
                    $query->where('manufacturer_id', request('manufacturer_id'));
                }

                if (request('status') == 'active') {
                    $query->where('status', true);
                } elseif (request('status') == 'inactive') {
                    $query->where('status', false);
                }
            })
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

