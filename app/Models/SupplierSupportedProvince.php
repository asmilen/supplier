<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierSupportedProvince extends Model
{
    protected $table = 'supplier_supported_province';

    protected $fillable = ['supplier_id', 'province_id', 'status'];

    function transportFee()
    {
        return $this->belongsTo(TransportFee::class, 'province_id', 'province_id');
    }
}
