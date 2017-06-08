<?php

namespace App\Models;

use Datatables;
use Illuminate\Database\Eloquent\Model;

class ProductCombo extends Model
{
    public function combo()
    {
        return $this->belongsTo(Combo::class);
    }


}
