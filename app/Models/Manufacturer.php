<?php

namespace App\Models;

use Datatables;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manufacturer extends BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($manufacturer) {
            if (! empty($manufacturer->code)) {
                return;
            }
            
            $name = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', trim($manufacturer->name))));

            if (($pos = strpos($name, '-')) === false) {
                $manufacturer->code = substr($name, 0, 6);
            } else {
                $manufacturer->code = substr($name, 0, 1).substr($name, $pos + 1, 5);
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public static function getDatatables()
    {
        $model = static::select([
                'id', 'code', 'name', 'homepage', 'status',
            ]);

        return Datatables::eloquent($model)
            ->editColumn('status', 'manufacturers.datatables.status')
            ->addColumn('action', 'manufacturers.datatables.action')
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public static function getActiveList()
    {
        return static::active()->pluck('name', 'id')->all();
    }
}
