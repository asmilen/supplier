<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    protected $truncates = [
        'activations',
        'persistences',
        'reminders',
        'roles',
        'role_users',
        'throttle',
        'users',
        'password_resets',
        'categories',
        'manufacturers',
    ];

    protected $seeders = [
        'UsersTableSeeder',
        'CategoriesTableSeeder',
        'ManufacturersTableSeeder',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($this->truncates as $table) {
            DB::table($table)->truncate();
        }

        foreach ($this->seeders as $seeder) {
            $this->call($seeder);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Model::reguard();
    }
}
