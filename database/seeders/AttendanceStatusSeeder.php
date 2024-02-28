<?php

namespace Database\Seeders;

use App\Models\AttendanceStatus;
use Illuminate\Database\Seeder;

class AttendanceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $account = AttendanceStatus::create([
            'id' => 1,
            'name' => 'Absent',
        ]);
        $account = AttendanceStatus::create([
            'id' => 2,
            'name' => 'Sick',
        ]);
        $account = AttendanceStatus::create([
            'id' => 3,
            'name' => 'Leave',
        ]);
        $account = AttendanceStatus::create([
            'id' => 4,
            'name' => 'Late Coming',
        ]);
        $account = AttendanceStatus::create([
            'id' => 5,
            'name' => 'Home work not done',
        ]);
        $account = AttendanceStatus::create([
            'id' => 6,
            'name' => 'Improper uniform',
        ]);
        $account = AttendanceStatus::create([
            'id' => 7,
            'name' => 'Test not prepared',
        ]);
    }
}
