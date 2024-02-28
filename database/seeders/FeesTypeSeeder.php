<?php

namespace Database\Seeders;

use App\Models\FeesType;
use Illuminate\Database\Seeder;

class FeesTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FeesType::create([
            'id' => 1,
            'name' => 'PROSPECTUS',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 2,
            'name' => 'REGISTRATION',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 3,
            'name' => 'ADMISSION FEE',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 4,
            'name' => 'MONTHLY FEE',
            'category' => 'VARIES',
        ]);
        FeesType::create([
            'id' => 5,
            'name' => 'ANNUAL FUND',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 6,
            'name' => 'HOSTEL ADMISSION FEE',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 7,
            'name' => 'HOSTEL FEE',
            'category' => 'VARIES',
        ]);
        FeesType::create([
            'id' => 8,
            'name' => 'OTHERS/FINE',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 9,
            'name' => 'RE-ADMISSION FEE',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 10,
            'name' => 'WORK BOOK CHARGES',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 11,
            'name' => 'EXAM STATIONARY CHARGES',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 12,
            'name' => 'SECOND SHIFT STUDY CHARGES',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 13,
            'name' => 'FAIL FINE',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 14,
            'name' => 'UNIFORM FINE',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 15,
            'name' => 'MISBEHAVE FINE',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 16,
            'name' => 'LATE FINE',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 17,
            'name' => 'ABSENT FINE',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 18,
            'name' => 'DISCIPLINE FINE',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 19,
            'name' => 'OTHER/MISC FINE',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 20,
            'name' => 'BUS FEE',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 21,
            'name' => 'SPORTS CHARGES',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 22,
            'name' => 'BOARD EXAM FEE',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 23,
            'name' => 'REMAINING BALANCE',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 24,
            'name' => 'PROMOTION FEE',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 25,
            'name' => 'UNIFORM CHARGES',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 26,
            'name' => 'SECURITY',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 27,
            'name' => 'DUPLICATE FEE BILL CHARGES',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 28,
            'name' => 'BOOKS CHARGES',
            'category' => 'ALL',
        ]);
        FeesType::create([
            'id' => 29,
            'name' => 'NOTE BOOKS CHARGES',
            'category' => 'ALL',
        ]);
    }
}
