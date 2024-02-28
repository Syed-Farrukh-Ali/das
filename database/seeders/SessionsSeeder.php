<?php

namespace Database\Seeders;

use App\Models\Session;
use Illuminate\Database\Seeder;

class SessionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Session::create([
            'id' => 1,
            'year' => '2019-2020',
        ]);
        Session::create([
            'id' => 2,
            'year' => '2020-2021',
        ]);
        Session::create([
            'id' => 3,
            'year' => '2021-2022',
        ]);
        Session::create([
            'id' => 4,
            'year' => '2022-2023',
            'active_financial_year' => true,
            'active_academic_year' => true,
        ]);
        Session::create([
            'id' => 5,
            'year' => '2023-2024',
        ]);
        Session::create([
            'id' => 6,
            'year' => '2024-2025',
        ]);
    }
}
