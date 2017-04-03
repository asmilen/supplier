<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;
use Illuminate\Auth\Access\AuthorizationException;

class Acl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! Sentinel::getUser()->hasAccess($request->route()->getName())) {
            throw new AuthorizationException;
        }

        return $next($request);
    }
}
