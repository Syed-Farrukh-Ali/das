<?php

namespace Database\Seeders;

use App\Models\PayScale;
use Illuminate\Database\Seeder;

class PayScaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $PayScale = PayScale::create([
            'payscale' => 2,
            'basic' => 12229,
            'increment' => 0,
            'maximum' => 0,
            'gp_fund' => 0,
            'welfare_fund' => 0,
        ]);

        $PayScale = PayScale::create([
            'payscale' => 4,
            'basic' => 12229,
            'increment' => 650,
            'maximum' => 10786,
            'gp_fund' => 100,
            'welfare_fund' => 0,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 5,
            'basic' => 12542,
            'increment' => 700,
            'maximum' => 11374,
            'gp_fund' => 100,
            'welfare_fund' => 0,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 6,
            'basic' => 12856,
            'increment' => 750,
            'maximum' => 11956,
            'gp_fund' => 100,
            'welfare_fund' => 0,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 7,
            'basic' => 13904,
            'increment' => 830,
            'maximum' => 13052,
            'gp_fund' => 100,
            'welfare_fund' => 0,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 8,
            'basic' => 15830,
            'increment' => 850,
            'maximum' => 14815,
            'gp_fund' => 300,
            'welfare_fund' => 100,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 9,
            'basic' => 16020,
            'increment' => 900,
            'maximum' => 15099,
            'gp_fund' => 300,
            'welfare_fund' => 100,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 10,
            'basic' => 16207,
            'increment' => 925,
            'maximum' => 15326,
            'gp_fund' => 300,
            'welfare_fund' => 100,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 11,
            'basic' => 16584,
            'increment' => 790,
            'maximum' => 15770,
            'gp_fund' => 300,
            'welfare_fund' => 100,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 12,
            'basic' => 16962,
            'increment' => 0,
            'maximum' => 16151,
            'gp_fund' => 0,
            'welfare_fund' => 0,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 13,
            'basic' => 17340,
            'increment' => 809,
            'maximum' => 16541,
            'gp_fund' => 300,
            'welfare_fund' => 100,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 14,
            'basic' => 22046,
            'increment' => 817,
            'maximum' => 18472,
            'gp_fund' => 300,
            'welfare_fund' => 100,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 15,
            'basic' => 23121,
            'increment' => 862,
            'maximum' => 19699,
            'gp_fund' => 300,
            'welfare_fund' => 100,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 16,
            'basic' => 24841,
            'increment' => 908,
            'maximum' => 21472,
            'gp_fund' => 300,
            'welfare_fund' => 100,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 17,
            'basic' => 26990,
            'increment' => 1153,
            'maximum' => 25011,
            'gp_fund' => 300,
            'welfare_fund' => 100,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 18,
            'basic' => 29570,
            'increment' => 1516,
            'maximum' => 29737,
            'gp_fund' => 300,
            'welfare_fund' => 100,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 19,
            'basic' => 32578,
            'increment' => 1879,
            'maximum' => 34828,
            'gp_fund' => 300,
            'welfare_fund' => 100,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 20,
            'basic' => 36018,
            'increment' => 2242,
            'maximum' => 40284,
            'gp_fund' => 300,
            'welfare_fund' => 100,
        ]);
        $PayScale = PayScale::create([
            'payscale' => 21,
            'basic' => 32887,
            'increment' => 2423,
            'maximum' => 44834,
            'gp_fund' => 300,
            'welfare_fund' => 100,
        ]);
    }
}
