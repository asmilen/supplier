<?php

namespace App\Models;

use Sentinel;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model)
        {
            $user = Sentinel::getUser() ?: auth()->user();

            if ($user) {
                $model->created_by = $user->id;

                $model->updated_by = $user->id;
            }
        });

        static::updating(function ($model)
        {
            $user = Sentinel::getUser() ?: auth()->user();

            if ($user) {
                $model->updated_by = $user->id;
            }
        });
    }
}
