<?php

namespace App\Models;

use Sentinel;
use Datatables;
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getDatatables()
    {
        $model = static::select('*')->with('user')
            ->orderBy('created_at', 'desc');

        return Datatables::eloquent($model)
            ->filter(function ($query) {
                $query->where(function ($query) {
                    $query->where('trackable_type', request('model_type'));
                });

                if (request()->has('model_id')) {
                    $query->where('trackable_id', request('model_id'));
                }
            })
            ->editColumn('user_id', function ($trackingLog) {
                return $trackingLog->user ? $trackingLog->user->name : $trackingLog->user_id;
            })
            ->make(true);
    }
}
