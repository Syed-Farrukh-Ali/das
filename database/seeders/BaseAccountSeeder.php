<?php

namespace Database\Seeders;

use App\Models\BaseAccount;
use Illuminate\Database\Seeder;

class BaseAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $account = BaseAccount::create([
            'id' => 1,
            'title' => 'Assets',
            'acode' => 1,
            'torise_debit' => 1,
        ]);
        $account = BaseAccount::create([
            'id' => 2,
            'title' => 'Liabilities',
            'acode' => 2,
            'torise_debit' => 0,
        ]);
        $account = BaseAccount::create([
            'id' => 3,
            'title' => 'Equity',
            'acode' => 3,
            'torise_debit' => 0,
        ]);
        $account = BaseAccount::create([
            'id' => 4,
            'title' => 'Revenue',
            'acode' => 4,
            'torise_debit' => 0,
        ]);
        $account = BaseAccount::create([
            'id' => 5,
            'title' => 'Expenses',
            'acode' => 5,
            'torise_debit' => 1,
        ]);
    }
}
