<?php

namespace App\Models;

use Sentinel;
use Illuminate\Database\Eloquent\Model;

class ModelTrackingLog extends Model
{
    public static function createByAction($model, $action)
    {
        $user = Sentinel::getUser() ?: auth()->user();

        return static::forceCreate([
            'trackable_id' => $model->id,
            'trackable_type' => get_class($model),
            'before' => json_encode(array_intersect_key($model->getOriginal(), $model->getDirty())),
            'after' => json_encode($model->getDirty()),
            'action' => $action,
            'user_id' => $user->id,
        ]);
    }
}
