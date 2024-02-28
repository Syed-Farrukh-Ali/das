<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $super_admin = User::create([
            'id' => 1,
            'first_name' => 'System',
            'last_name' => 'Admin',
            'email' => 'admin@system.com',
            'active' => 1,
            'password' => Hash::make('password'),
            'remember_token' => null,
        ]);

        $super_admin->assignRole(config('access.users.super_admin'));
        $super_admin->givePermissionTo('view backend');
    }
}
