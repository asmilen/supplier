<?php

namespace App\Http\Middleware;

use GuzzleHttp\Exception\ClientException;
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
        if ($this->shouldLoginViaTekoSSO()) {
            $this->loginViaTekoSSO();
        }

        return $next($request);
    }

    protected function shouldLoginViaTekoSSO()
    {
        return isset($_COOKIE['_uat']) && Sentinel::guest();
    }

    protected function loginViaTekoSSO()
    {
        if (! $userInfo = $this->getUserInfoByTekoSSOCookie()) {
            unset($_COOKIE['_uat']);
            return false;
        }

        $user = User::where('email', $userInfo['email'])->first();

        if (! $user) {
            $user = User::forceCreate([
                'email' => $userInfo['email'],
                'name' => $userInfo['name'],
            ]);
        }

        Sentinel::login($user);

        return true;
    }

    protected function getUserInfoByTekoSSOCookie()
    {
        $client = new Client([
            'base_uri' => env('TEKO_ACC_URL'),
        ]);

        try{
            $response = $client->get('/api/validate_access_token?accessToken='.$_COOKIE['_uat']);

            $userInfo = json_decode($response->getBody()->getContents(), true);

            if (isset($userInfo['error']) || empty($userInfo['email'])) {
                return false;
            }

            return $userInfo;
        }
        catch (ClientException $e){

        }

        return false;
    }
}
