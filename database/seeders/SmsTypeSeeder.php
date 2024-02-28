<?php

namespace Database\Seeders;

use App\Models\SMS\SmsType;
use Illuminate\Database\Seeder;

class SmsTypeSeeder extends Seeder
{
    public function run()
    {
        SmsType::create([
            'name' => "Admission",
        ]);

        SmsType::create([
            'name' => "Attendance",
        ]);

        SmsType::create([
            'name' => "Result",
        ]);

        SmsType::create([
            'name' => "Due Fee",
        ]);

        SmsType::create([
            'name' => "Custom",
        ]);

        SmsType::create([
            'name' => "Appointment",
        ]);
    }
}
