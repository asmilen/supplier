<?php

namespace App\Models;

use Datatables;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    public function addresses()
    {
        return $this->hasMany(SupplierAddress::class);
    }

    public function suppliers_supported_provinces()
    {
        return $this->belongsToMany(Province::class,'supplier_supported_province','supplier_id','province_id');
    }

    public function supplier_bank()
    {
        return $this->belongsTo(SupplierBankAccount::class,'id','supplier_id');
    }

    public static function getDatatables()
    {
        $model = static::select([
            'id','name','code','tax_number','status'
        ])->with('addresses');

        return Datatables::eloquent($model)
            ->filter(function ($query) {
                if (request()->has('keyword')) {
                    $query->where('name', 'like', '%'.request('keyword').'%');
                }
            })
            ->editColumn('province', function ($model) {
                return $model->suppliers_supported_provinces ? $model->suppliers_supported_provinces->first()['name'] : '';
            })
            ->editColumn('address', function ($model) {
                return $model->addresses()->first() ? $model->addresses()->first()->address : '';
            })
            ->editColumn('info_person', function ($model) {
                $string = $model->addresses()->first() ? $model->addresses()->first()->contact_name : '';
                $string .= ' - ';
                $string .= $model->addresses()->first() ? $model->addresses()->first()->contact_phone : '';
                return $string;
            })
            ->editColumn('status', 'products.datatables.status')
            ->addColumn('action', 'suppliers.datatables.action')
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
}

