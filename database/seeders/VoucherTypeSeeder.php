<?php

namespace Database\Seeders;

use App\Models\VoucherType;
use Illuminate\Database\Seeder;

class VoucherTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $voucherType = VoucherType::create([
            'id' => 1,
            'name' => 'CR',

        ]);
        $voucherType = VoucherType::create([
            'id' => 2,
            'name' => 'CP',

        ]);
        $voucherType = VoucherType::create([
            'id' => 3,
            'name' => 'BR',

        ]);
        $voucherType = VoucherType::create([
            'id' => 4,
            'name' => 'BP',

        ]);
        $voucherType = VoucherType::create([
            'id' => 5,
            'name' => 'JV',

        ]);
        $voucherType = VoucherType::create([
            'id' => 6,
            'name' => 'DN',

        ]);
    }
}
