<?php

namespace App\Models;

use Sentinel;
use Activation;
use Datatables;
use Illuminate\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;
use Cartalyst\Sentinel\Users\EloquentUser;
use Illuminate\Auth\Passwords\CanResetPassword;
use App\Exceptions\ModelShouldNotDeletedException;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends EloquentUser implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable,
        Authorizable,
        CanResetPassword,
        Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'permissions',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->setApiToken();
        });
    }

    public function getAvatarAttribute()
    {
        if (! empty($this->attributes['avatar'])) {
            return $this->attributes['avatar'];
        }

        $hash = md5(strtolower(trim($this->attributes['email'])));

        return "http://www.gravatar.com/avatar/$hash";
    }

    public function hasAccess($permissions)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return parent::hasAccess($permissions);
    }

    public function hasAnyAccess($permissions)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return parent::hasAnyAccess($permissions);
    }

    public static function getDatatables()
    {
        $model = static::select([
                'id', 'name', 'email', 'last_login'
            ])
            ->with('roles', 'activations');

        return Datatables::eloquent($model)
            ->filter(function ($query) {
                if (request()->has('name')) {
                    $query->where(function ($query) {
                        $query->where('name', 'like', '%'.request('name').'%');
                    });
                }

                if (request()->has('email')) {
                    $query->where('email', 'like', '%'.request('email').'%');
                }
            })
            ->editColumn('roles', function ($user) {
                return implode(', ', $user->roles->map(function ($role) {
                    return $role->name;
                })->toArray());
            })
            ->addColumn('status', 'users.datatables.status')
            ->addColumn('action', 'users.datatables.action')
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function getRolesList()
    {
        return $this->roles()->pluck('id')->toArray();
    }

    public function isActive()
    {
        return Activation::completed($this);
    }

    public function setActivation($active = true)
    {
        if ($active && ! $this->isActive()) {
            return $this->activate();
        }

        if (! $active) {
            return $this->deactivate();
        }

        return $this;
    }

    public function activate()
    {
        $activation = Activation::exists($this);

        if (! $activation) {
            $activation = Activation::create($this);
        }

        Activation::complete($this, $activation->code);

        return $this;
    }

    public function deactivate()
    {
        Activation::remove($this);

        return $this;
    }

    public function syncRoles($roleIds)
    {
        $this->roles()->sync($roleIds);

        return $this;
    }

    public function update(array $attributes = [], array $options = [])
    {
        if (empty($attributes['password'])) {
            unset($attributes['password']);
        }

        Sentinel::update($this, $attributes);

        return $this;
    }

    public function delete()
    {
        if ($this->id == Sentinel::getUser()->id) {
            throw new ModelShouldNotDeletedException('Cannot delete yourself.');
        }

        if ($this->isSuperAdmin()) {
            throw new ModelShouldNotDeletedException('Cannot delete Super Admin.');
        }

        return parent::delete();
    }

    public function isSuperAdmin()
    {
        return !! $this->is_superadmin;
    }

    public function grantPermissions($permissions)
    {
        foreach ($permissions as $permission => $value) {
            $this->grantPermission($permission, $value);
        }

        $this->save();

        return $this;
    }

    protected function grantPermission($permission, $value)
    {
        return ($value == -1)
            ? $this->removePermission($permission)
            : $this->updatePermission($permission, !! $value, true);
    }

    public function setApiToken($force = false)
    {
        if ($force || empty($this->api_token)) {
            do {
                $token = str_random(60);
            } while (static::where('api_token', $token)->first());

            $this->api_token = $token;
        }

        return $this;
    }

    public function setUserSupportedProvince($level,$areas)
    {
        UserSupportedProvince::where('supported_id',$this->id)->delete();

        if ($level == 1) {
            foreach($areas as $regionID)
            {
                UserSupportedProvince::forceCreate([
                    'supported_id' => $this->id,
                    'status'       => true,
                    'level' => $level,
                    'region_id' => $regionID,
                    'province_id' => 0,
                ]);
            }
        } else {
            foreach($areas as $provinceID)
            {
                $regionId = Province::find($provinceID)->region_id;

                UserSupportedProvince::forceCreate([
                    'supported_id' => $this->id,
                    'status'       => true,
                    'level' => $level,
                    'region_id' => $regionId,
                    'province_id' => $provinceID,
                ]);
            }
        }
    }

    public function userSupportedProvince()
    {
        return $this->hasMany(UserSupportedProvince::class,'supported_id','id');
    }
}
