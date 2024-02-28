<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Designation::create([
            'id' => 1,
            'name' => 'TECHNICAL STAFF',
        ]);
        Designation::create([
            'id' => 2,
            'name' => 'ASSTT ACCOUNT',
        ]);
        Designation::create([
            'id' => 3,
            'name' => 'COMPUTER OPERATOR',
        ]);
        Designation::create([
            'id' => 4,
            'name' => 'LIBRARIAN',
        ]);
        Designation::create([
            'id' => 5,
            'name' => 'ADMIN CLERK',
        ]);
        Designation::create([
            'id' => 6,
            'name' => 'LAB ASSISTANT',
        ]);
        Designation::create([
            'id' => 7,
            'name' => 'CLASS IV',
        ]);
        Designation::create([
            'id' => 8,
            'name' => 'SWEEPER',
        ]);
        Designation::create([
            'id' => 9,
            'name' => 'P.E.T.',
        ]);
        Designation::create([
            'id' => 10,
            'name' => 'SENIOR TEACHER',
        ]);
        Designation::create([
            'id' => 11,
            'name' => 'TEACHER',
        ]);
        Designation::create([
            'id' => 12,
            'name' => 'STORE KEEPER',
        ]);
        Designation::create([
            'id' => 13,
            'name' => 'CONTROLLER OF EXAMINATIONS',
        ]);
        Designation::create([
            'id' => 14,
            'name' => 'QARI',
        ]);
        Designation::create([
            'id' => 15,
            'name' => 'SENIOR SCIENCE TEACHER',
        ]);
        Designation::create([
            'id' => 16,
            'name' => 'HOSTEL ADMINISTRATOR',
        ]);
        Designation::create([
            'id' => 17,
            'name' => 'HOSTEL SUPERINTENDENT',
        ]);
        Designation::create([
            'id' => 18,
            'name' => 'QARIA',
        ]);
        Designation::create([
            'id' => 19,
            'name' => 'ADMINISTRATOR',
        ]);
        Designation::create([
            'id' => 20,
            'name' => 'DIRECTOR ',
        ]);
        Designation::create([
            'id' => 21,
            'name' => 'CO-ORDINATOR',
        ]);
        Designation::create([
            'id' => 22,
            'name' => 'TEACHING ASSISTANT',
        ]);
        Designation::create([
            'id' => 23,
            'name' => 'MARKETING EXECUTIVE',
        ]);
        Designation::create([
            'id' => 24,
            'name' => 'VICE PRINCIPAL',
        ]);
        Designation::create([
            'id' => 25,
            'name' => 'LECTURER ASSISTANT',
        ]);
        Designation::create([
            'id' => 26,
            'name' => 'DRIVER',
        ]);
        Designation::create([
            'id' => 27,
            'name' => 'COOK   ',
        ]);
        Designation::create([
            'id' => 28,
            'name' => 'HEAD COOK ',
        ]);
        Designation::create([
            'id' => 29,
            'name' => 'CIVIL SUB ENGINER',
        ]);
        Designation::create([
            'id' => 30,
            'name' => 'HEAD IT DEPARTMENT',
        ]);
        Designation::create([
            'id' => 31,
            'name' => 'ACCOUNTANT',
        ]);
        Designation::create([
            'id' => 32,
            'name' => 'PRINCIPAL',
        ]);
    }
}
