<?php

namespace Database\Seeders;

use App\Models\ExamType;
use Illuminate\Database\Seeder;

class ExamTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ExamType::create([
            'id' => 1,
            'name' => 'FIRST TERM',
        ]);
        ExamType::create([
            'id' => 2,
            'name' => 'SECOND TERM',
        ]);
        ExamType::create([
            'id' => 3,
            'name' => 'ANNUAL TERM',
        ]);
        ExamType::create([
            'id' => 4,
            'name' => 'MID TERM 1',
        ]);
        ExamType::create([
            'id' => 5,
            'name' => 'MID TERM 2',
        ]);
        ExamType::create([
            'id' => 6,
            'name' => 'PRE-BOARD',
        ]);
        ExamType::create([
            'id' => 7,
            'name' => 'PRE-BOARD PHASE-2',
        ]);
        ExamType::create([
            'id' => 8,
            'name' => 'JANUARY',
        ]);
        ExamType::create([
            'id' => 9,
            'name' => 'FEBRUARY',
        ]);
        ExamType::create([
            'id' => 10,
            'name' => 'MARCH',
        ]);
        ExamType::create([
            'id' => 11,
            'name' => 'APRIL',
        ]);
        ExamType::create([
            'id' => 12,
            'name' => 'MAY',
        ]);
        ExamType::create([
            'id' => 13,
            'name' => 'JUNE',
        ]);
        ExamType::create([
            'id' => 14,
            'name' => 'JULY',
        ]);
        ExamType::create([
            'id' => 15,
            'name' => 'AUGUST',
        ]);
        ExamType::create([
            'id' => 16,
            'name' => 'SEPTEMBER',
        ]);
        ExamType::create([
            'id' => 17,
            'name' => 'OCTOBER',
        ]);
        ExamType::create([
            'id' => 18,
            'name' => 'NOVEMBER',
        ]);
        ExamType::create([
            'id' => 19,
            'name' => 'DECEMBER',
        ]);
        ExamType::create([
            'id' => 20,
            'name' => 'WEEKLY EXAM',
        ]);
        ExamType::create([
            'id' => 21,
            'name' => 'DAILY EXAM',
        ]);
    }
}
