<?php

namespace Database\Seeders;

use App\Models\GlobalBank;
use Illuminate\Database\Seeder;

class GlobalBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GlobalBank::create([
            'name' => 'Allied bank limited',
        ]);
        GlobalBank::create([
            'name' => 'Askari Bank Limited',
        ]);
        GlobalBank::create([
            'name' => 'Bank AL Habib Limited',
        ]);
        GlobalBank::create([
            'name' => 'Habib Bank Limited',
        ]);
        GlobalBank::create([
            'name' => 'MCB Bank Limited',
        ]);
        GlobalBank::create([
            'name' => 'Meezan Bank Limited',
        ]);
        GlobalBank::create([
            'name' => 'UBL bank limited',
        ]);
        GlobalBank::create([
            'name' => 'The Bank of Punjab',
        ]);
    }
}
