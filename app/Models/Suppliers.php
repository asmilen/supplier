<?php

namespace App\Models;

use Datatables;
use Illuminate\Database\Eloquent\Model;

class Suppliers extends Model
{
    protected $table = 'suppliers';

    public function suppliers_addresses()
    {
        return $this->belongsTo(SupplierAddress::class,'id','supplier_id');
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
        ])->with('suppliers_addresses');

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
                return $model->suppliers_addresses ? $model->suppliers_addresses->address : '';
            })
            ->editColumn('info_person', function ($model) {
                $string = $model->suppliers_addresses ? $model->suppliers_addresses->contact_name : '';
                $string .= ' - ';
                $string .= $model->suppliers_addresses ? $model->suppliers_addresses->contact_phone : '';
                return $string;
            })
            ->editColumn('status', 'products.datatables.status')
            ->addColumn('action', 'suppliers.datatables.action')
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
}

