<?php

namespace App\Http\Controllers\Auth;

use Sentinel;
use Socialite;
use App\Models\User;
use GuzzleHttp\Client;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Redirect the user to the Socialite provider authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Socialite provider.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
        $googleUser = Socialite::driver('google')->user();

        $user = Sentinel::findByCredentials(['email' => $googleUser->email]);

        if (! $user) {
            $user = Sentinel::registerAndActivate([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'password' => str_random(10),
                'is_superadmin' => true,
            ]);
        }

        Sentinel::login($user);

        return redirect('/dashboard');
    }

    public function handleTekoCallback()
    {
        $client = new Client([
            'base_uri' => env('TEKO_ACC_URL'),
        ]);

        $response = $client->get('/api/validate_access_token?accessToken='.$_COOKIE['_uat']);

        $userInfo = json_decode($response->getBody()->getContents(), true);

        if (isset($userInfo['error'])) {
            return redirect('/login');
        }

        $user = User::where('email', $userInfo['email'])->first();

        if (! $user) {
            $user = User::forceCreate([
                'email' => $userInfo['email'],
                'name' => $userInfo['name'],
            ]);
        }

        Sentinel::login($user);

        return redirect('/dashboard');
    }
}
