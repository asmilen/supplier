<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierAddress extends Model
{
    use HasUpdater;

    protected $table = 'supplier_addresses';
}
