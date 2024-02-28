<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Course::create([
            'id' => 1,
            'name' => 'SCIENCE GROUP',
        ]);
        Course::create([
            'id' => 2,
            'name' => 'ARTS GROUP',
        ]);
        Course::create([
            'id' => 3,
            'name' => 'GENERAL EDUCATION',
        ]);
    }
}
