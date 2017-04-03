<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sentinel = app('Cartalyst\Sentinel\Sentinel');

        $sentinel->registerAndActivate([
            'name' => 'Giang Thai Cuong',
            'email' => 'cuong.gt@teko.vn',
            'password' => 'secret',
            'api_token' => str_random(60),
            'is_superadmin' => true,
        ]);
    }
}
