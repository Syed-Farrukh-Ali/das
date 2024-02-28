<?php

namespace Database\Seeders;

use App\Models\StudentClass;
use Illuminate\Database\Seeder;

class StudentClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StudentClass::create([
            'id' => 1,
            'name' => 'HIFZ',
        ]);
        StudentClass::create([
            'id' => 2,
            'name' => 'PLAY GROUP',
        ]);
        StudentClass::create([
            'id' => 3,
            'name' => 'NURSERY',
        ]);
        StudentClass::create([
            'id' => 4,
            'name' => 'PREP',
        ]);
        StudentClass::create([
            'id' => 5,
            'name' => 'ONE',
        ]);
        StudentClass::create([
            'id' => 6,
            'name' => 'TWO',
        ]);
        StudentClass::create([
            'id' => 7,
            'name' => 'THREE',
        ]);
        StudentClass::create([
            'id' => 8,
            'name' => '4TH',
        ]);
        StudentClass::create([
            'id' => 9,
            'name' => '5TH',
        ]);
        StudentClass::create([
            'id' => 10,
            'name' => '6TH',
        ]);
        StudentClass::create([
            'id' => 11,
            'name' => '7TH',
        ]);
        StudentClass::create([
            'id' => 12,
            'name' => '8TH',
        ]);
        StudentClass::create([
            'id' => 13,
            'name' => '9TH',
        ]);
        StudentClass::create([
            'id' => 14,
            'name' => '10TH',
        ]);
    }
}
