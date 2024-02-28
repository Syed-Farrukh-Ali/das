<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('roles')->insert([
            [
                'name' => 'Super Admin',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Head Office',
                'guard_name' => 'web',
            ],

            [
                'name' => 'Campus',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Teacher',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Staff Member',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Parent',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Student',
                'guard_name' => 'web',
            ],
        ]);

        Permission::insert([
            [
                'guard_name' => 'web',
                'name' => 'view backend',
            ],
            [
                'guard_name' => 'web',
                'name' => 'view frontend',
            ],
        ]);
    }
}
