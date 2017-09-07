<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogOffSupplier extends Model
{

    protected $table = 'log_off_suppliers';
    protected $fillable = [
        'supplier_id',
        'type',
        'post_data',
        'response',
        'created_at',
        'updated_at'
    ];
}
