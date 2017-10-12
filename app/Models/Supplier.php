<?php

namespace App\Models;

use Datatables;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use Trackable, HasUpdater;

    protected $with = ['supplierSupportedProvince', 'address'];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    public function addresses()
    {
        return $this->hasMany(SupplierAddress::class);
    }

    public function address()
    {
        return $this->hasOne(SupplierAddress::class);
    }

    public function supplierSupportedProvince()
    {
        return $this->belongsToMany(Province::class, 'supplier_supported_province', 'supplier_id', 'province_id');
    }

    public function supplier_bank()
    {
        return $this->belongsTo(SupplierBankAccount::class, 'id', 'supplier_id');
    }

    public function scopeHasNoProducts($query)
    {
        return $query->where('suppliers.status', true)
            ->leftJoin('product_supplier', 'suppliers.id', '=', 'product_supplier.supplier_id')
            ->whereNull('product_supplier.id');
    }
}
