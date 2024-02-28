<?php

namespace Database\Seeders;

use App\Models\JobStatus;
use Illuminate\Database\Seeder;

class JobStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        JobStatus::Create([
            'id' => 1,
            'name' => 'inservice',
        ]);
        JobStatus::Create([
            'id' => 2,
            'name' => 'retired',
        ]);
        JobStatus::Create([
            'id' => 3,
            'name' => 'transfered',
        ]);
        JobStatus::Create([
            'id' => 4,
            'name' => 'struck off',
        ]);
        JobStatus::Create([
            'id' => 5,
            'name' => 'Registered only',
        ]);
    }
}
