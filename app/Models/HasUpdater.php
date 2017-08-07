<?php

namespace App\Models;

use Sentinel;

trait HasUpdater
{
    public static function bootHasUpdater()
    {
        $user = Sentinel::getUser() ?: auth()->user();

        if (! $user) {
            return;
        }

        static::creating(function ($model) use ($user) {
            $model->created_by = $user->id;

            $model->updated_by = $user->id;
        });

        static::updating(function ($model) use ($user) {
            $model->updated_by = $user->id;
        });
    }
}
