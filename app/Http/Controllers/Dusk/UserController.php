<?php

namespace App\Http\Controllers\Dusk;

use Sentinel;
use Illuminate\Support\Facades\Auth;

class UserController
{
    /**
     * Retrieve the authenticated user identifier and class name.
     *
     * @param  string|null  $guard
     * @return array
     */
    public function user($guard = null)
    {
        if (empty($guard) || $guard == 'web') {
            $user = Sentinel::getUser();
        } else {
            $user = Auth::guard($guard)->user();
        }

        if (! $user) {
            return [];
        }

        return [
            'id' => $user->getAuthIdentifier(),
            'className' => get_class($user),
        ];
    }

    /**
     * Login using the given user ID / email.
     *
     * @param  string  $userId
     * @param  string  $guard
     * @return Response
     */
    public function login($userId, $guard = null)
    {
        $model = $this->modelForGuard(
            $guard = $guard ?: config('auth.defaults.guard')
        );

        if (str_contains($userId, '@')) {
            $user = (new $model)->where('email', $userId)->first();
        } else {
            $user = (new $model)->find($userId);
        }

        if ($guard == 'web') {
            Sentinel::login($user);
        } else {
            Auth::guard($guard)->login($user);
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param  string  $guard
     * @return Response
     */
    public function logout($guard = null)
    {
        $guard = $guard ?: config('auth.defaults.guard');

        if ($guard == 'web') {
            Sentinel::logout();
        } else {
            Auth::guard($guard ?: config('auth.defaults.guard'))->logout();
        }
    }

    /**
     * Get the model for the given guard.
     *
     * @param  string  $guard
     * @return string
     */
    protected function modelForGuard($guard)
    {
        $provider = config("auth.guards.{$guard}.provider");

        return config("auth.providers.{$provider}.model");
    }
}
