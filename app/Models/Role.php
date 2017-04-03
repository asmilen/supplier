<?php

namespace App\Models;

use Datatables;
use Cartalyst\Sentinel\Roles\EloquentRole;
use Cviebrock\EloquentSluggable\Sluggable;

class Role extends EloquentRole
{
    use Sluggable;

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name',
                'onUpdate' => true,
            ]
        ];
    }

    public static function getDatatables()
    {
        $model = static::select([
                'id', 'slug', 'name', 'permissions'
            ]);

        return Datatables::eloquent($model)
            ->addColumn('action', 'roles.datatables.action')
            ->make(true);
    }

    public function grantPermissions(array $permissions)
    {
        $this->fill([
            'permissions' => array_map(function ($value) {
                return $value == 1;
            }, $permissions),
        ])->save();

        return $this;
    }
}
