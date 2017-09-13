<?php

namespace App\Models;

use Datatables;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use Trackable, HasUpdater;

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function addresses()
    {
        return $this->hasMany(SupplierAddress::class);
    }

    public function address()
    {
        return $this->hasOne(SupplierAddress::class);
    }

    public function suppliers_supported_provinces()
    {
        return $this->belongsToMany(Province::class, 'supplier_supported_province', 'supplier_id', 'province_id');
    }

    public function supplier_bank()
    {
        return $this->belongsTo(SupplierBankAccount::class, 'id', 'supplier_id');
    }

    public static function getDatatables()
    {
        $model = static::select([
            'id', 'name', 'full_name', 'code', 'tax_number', 'status', 'type', 'sup_type', 'price_active_time'
        ])->with('addresses', 'address', 'suppliers_supported_provinces');

        return Datatables::eloquent($model)
            ->filter(function ($query) {
                if (request()->has('keyword')) {
                    $query->where('name', 'like', '%' . request('keyword') . '%');
                }

                if (request()->has('typeId')) {
                    $query->where('type', request('typeId'));
                }

                if (request()->has('province')) {
                    $query->where('type', request('typeId'));
                }

                if (request('status') == 'active') {
                    $query->where('status', true);
                } elseif (request('status') == 'inactive') {
                    $query->where('status', false);
                }
            })
            ->editColumn('province', function ($model) {
                return $model->suppliers_supported_provinces ? $model->suppliers_supported_provinces->first()['name'] : '';
            })
            ->editColumn('address', function ($model) {
                return $model->address ? $model->address->address : '';
            })
            ->editColumn('info_person', function ($model) {
                $string = $model->address ? $model->address->contact_name : '';
                $string .= ' - ';
                $string .= $model->address ? $model->address->contact_phone : '';
                return $string;
            })
            ->editColumn('price_active_time', function ($model) {
                return ($model->price_active_time / 24) . ' ngày';
            })
            ->editColumn('sup_type', function ($model) {
                if ($model->sup_type == 2) {
                    return 'Ký gửi';
                } elseif ($model->sup_type == 1)
                    return 'Hàng mua';

            })
            ->editColumn('status', 'products.datatables.status')
            ->addColumn('action', 'suppliers.datatables.action')
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function scopeHasNoProducts($query)
    {
        return $query->where('suppliers.status', true)
            ->leftJoin('product_supplier', 'suppliers.id', '=', 'product_supplier.supplier_id')
            ->whereNull('product_supplier.id');
    }
}
