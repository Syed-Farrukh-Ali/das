<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Concession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CampusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'first_name' => 'head',
            'last_name' => 'office',
            'email' => 'headoffice@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('Head Office');

        $headoffice = $user->head_office()->create([
            'user_id' => $user->id,
            'title' => 'Head Office',
            'address' => 'address',
            'city' => 'Lahore',
            'province' => 'punjab',
            'longitude' => '1232',
            'latitude' => '3434',
        ]);
        $concession = Concession::create([
            'title' => 'General concession',
            'amount' => 300,
        ]);
        $concession = Concession::create([
            'title' => 'staff child concession',
            'amount' => 300,
        ]);

        // $campus_user = User::create([
        //     "id" => 10,
        //     "first_name" => "principal_1",
        //     "last_name" => "campus_1",
        //     "email" => "campus_1@system.com",
        //     "active" => 1,
        //     'password' => Hash::make('password'),
        //     "remember_token" => null,
        // ]);

        // $campus_user->assignRole(config('access.users.campus'));
        // $campus_user->givePermissionTo('view backend');

        // Campus::create([
        //     "id" => 1,
        //     "user_id" => 10,
        //     "head_office_id" => 1,

        //     "name" => 'gulberg_girls_campus_1',
        //     "area" => 'gulberg',
        //     "city" => 'lahore',
        //     "province" => 'punjab',

        // ]);

        // $campus_user = User::create([
        //     "id" => 11,
        //     "first_name" => "principal_2",
        //     "last_name" => "campus_2",
        //     "email" => "campus_2@system.com",
        //     "active" => 1,
        //     'password' => Hash::make('password'),
        //     "remember_token" => null,
        // ]);

        // $campus_user->assignRole(config('access.users.campus'));
        // $campus_user->givePermissionTo('view backend');

        // Campus::create([
        //     "id" => 2,
        //     "user_id" => 11,
        //     "head_office_id" => 1,

        //     "name" => 'gulberg_boys_campus_2',
        //     "area" => 'modeltown',
        //     "city" => 'lahore',
        //     "province" => 'punjab',

        // ]);
    }
}
