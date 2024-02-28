<?php

namespace Database\Seeders;

use App\Models\AccountGroup;
use Illuminate\Database\Seeder;

class AccountGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $account = AccountGroup::create([
            'id' => 1,
            'base_account_id' => 1,
            'acode' => 11,
            'title' => 'Non current Assets',
            'torise_debit' => 1,
        ]);
        $account = AccountGroup::create([
            'id' => 2,
            'base_account_id' => 1,
            'acode' => 12,
            'title' => 'Long Term deposite',
            'torise_debit' => 1,
        ]);
        $account = AccountGroup::create([
            'id' => 3,
            'base_account_id' => 1,
            'acode' => 13,
            'title' => 'Current Assets',
            'torise_debit' => 1,
        ]);
        $account = AccountGroup::create([
            'id' => 4,
            'base_account_id' => 1,
            'acode' => 14,
            'title' => 'Cash & Bank Balances',
            'torise_debit' => 1,
        ]);
        $account = AccountGroup::create([
            'id' => 5,
            'base_account_id' => 2,
            'acode' => 21,
            'title' => 'Long Term Liabilities',
            'torise_debit' => 0,
        ]);
        $account = AccountGroup::create([
            'id' => 6,
            'base_account_id' => 2,
            'acode' => 22,
            'title' => 'Short Term Liabilities',
            'torise_debit' => 0,
        ]);
        $account = AccountGroup::create([
            'id' => 7,
            'base_account_id' => 3,
            'acode' => 31,
            'title' => 'Reservers & Funds',
            'torise_debit' => 0,
        ]);
        $account = AccountGroup::create([
            'id' => 8,
            'base_account_id' => 4,
            'acode' => 42,
            'title' => 'Fee',
            'torise_debit' => 0,
        ]);
        $account = AccountGroup::create([
            'id' => 9,
            'base_account_id' => 4,
            'acode' => 43,
            'title' => 'Other Receipts',
            'torise_debit' => 0,
        ]);
        $account = AccountGroup::create([
            'id' => 10,
            'base_account_id' => 5,
            'acode' => 51,
            'title' => 'School Expense',
            'torise_debit' => 1,
        ]);
        $account = AccountGroup::create([
            'id' => 11,
            'base_account_id' => 5,
            'acode' => 53,
            'title' => 'Hostel Expense',
            'torise_debit' => 1,
        ]);
        $account = AccountGroup::create([
            'id' => 12,
            'base_account_id' => 5,
            'acode' => 55,
            'title' => 'Society Expense',
            'torise_debit' => 1,
        ]);
    }
}
