<?php

namespace App\Http\Middleware;

use Sentinel;
use Closure;
use App\Models\User;
use GuzzleHttp\Client;

class SSOLogin
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
        if (isset($_COOKIE['_uat']) && !Sentinel::check())
        {
            $client = new Client([
                'base_uri' => env('TEKO_ACC_URL'),
            ]);

            $response = $client->get('/api/validate_access_token?accessToken='.$_COOKIE['_uat']);

            $userInfo = json_decode($response->getBody()->getContents(), true);

            if (! isset($userInfo['error'])) {
                $user = User::where('email', $userInfo['email'])->first();

                if (! $user) {
                    $user = User::forceCreate([
                        'email' => $userInfo['email'],
                        'name' => $userInfo['name'],
                    ]);
                }

                Sentinel::login($user);

            }
        }

        return $next($request);
    }
}
