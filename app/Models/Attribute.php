<?php

namespace App\Models;

use Datatables;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Attribute extends Model
{
    use Sluggable;

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name',
            ]
        ];
    }

    public static function getDatatables()
    {
        $model = static::select([
                'id', 'name', 'slug', 'updated_at',
            ]);

        return Datatables::eloquent($model)
            ->addColumn('action', 'attributes.datatables.action')
            ->make(true);
    }

    public static function getList()
    {
        return static::pluck('name', 'id')->all();
    }
}
