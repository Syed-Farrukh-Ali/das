<?php

namespace Database\Seeders;

use App\Models\AccountChart;
use App\Models\BankAccount;
use App\Models\HighestValue;
use Illuminate\Database\Seeder;

class AccountChartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        HighestValue::created([
            'admission_id' => '1001', // for icrementing admisison id for student
        ]);
        $account = AccountChart::create([
            'id' => 1,
            'account_group_id' => 1,
            'acode' => 1101,
            'title' => 'land',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 2,
            'account_group_id' => 1,
            'acode' => 1102,
            'title' => 'building',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 3,
            'account_group_id' => 1,
            'acode' => 1103,
            'title' => 'furniture and fixture',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 4,
            'account_group_id' => 1,
            'acode' => 1104,
            'title' => 'office equipments',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 5,
            'account_group_id' => 1,
            'acode' => 1105,
            'title' => 'computer',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 6,
            'account_group_id' => 1,
            'acode' => 1106,
            'title' => 'electric fitting and istallation',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 7,
            'account_group_id' => 1,
            'acode' => 1107,
            'title' => 'laboratory equipment',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 8,
            'account_group_id' => 1,
            'acode' => 1108,
            'title' => 'books',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 9,
            'account_group_id' => 1,
            'acode' => 1109,
            'title' => 'vehicles',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 10,
            'account_group_id' => 1,
            'acode' => 1110,
            'title' => 'cycles',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 11,
            'account_group_id' => 1,
            'acode' => 1111,
            'title' => 'generators',
            'torise_debit' => 1,
        ]);

        /////////////
        $account = AccountChart::create([
            'id' => 12,
            'account_group_id' => 2,
            'acode' => 1201,
            'title' => 'security deposites',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 13,
            'account_group_id' => 2,
            'acode' => 1202,
            'title' => 'investments',
            'torise_debit' => 1,
        ]);
        ///////////////////////
        $account = AccountChart::create([
            'id' => 14,
            'account_group_id' => 3,
            'acode' => 1301,
            'title' => 'advance to school staff against salaries',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 15,
            'account_group_id' => 3,
            'acode' => 1302,
            'title' => 'advance to staff for expense',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 16,
            'account_group_id' => 3,
            'acode' => 1303,
            'title' => 'advance to campuses',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 17,
            'account_group_id' => 3,
            'acode' => 1304,
            'title' => 'advance to branches',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 18,
            'account_group_id' => 3,
            'acode' => 1305,
            'title' => 'advance to directors',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 19,
            'account_group_id' => 3,
            'acode' => 1306,
            'title' => 'advance to venders',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 20,
            'account_group_id' => 3,
            'acode' => 1307,
            'title' => 'advance to others',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 21,
            'account_group_id' => 3,
            'acode' => 1308,
            'title' => 'prepaid expense',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 22,
            'account_group_id' => 3,
            'acode' => 1309,
            'title' => 'store',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 23,
            'account_group_id' => 3,
            'acode' => 1310,
            'title' => 'deffered cost',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 24,
            'account_group_id' => 3,
            'acode' => 1311,
            'title' => 'advance to members',
            'torise_debit' => 1,
        ]);
        //////////////////////////////////
        $account = AccountChart::create([
            'id' => 25,
            'account_group_id' => 4,
            'acode' => 1401,
            'title' => 'Banks',
            'torise_debit' => 1,
        ]);
        $cash_account = AccountChart::create([
            'id' => 26,
            'account_group_id' => 4,
            'acode' => 1402,
            'title' => 'Cash in hands',
            'torise_debit' => 1,
        ]);

        //////////////////////
        $account = AccountChart::create([
            'id' => 27,
            'account_group_id' => 5,
            'acode' => 2101,
            'title' => 'Capital',
            'torise_debit' => 0,
        ]);
        $account = AccountChart::create([
            'id' => 28,
            'account_group_id' => 5,
            'acode' => 2102,
            'title' => 'Loan members',
            'torise_debit' => 0,
        ]);
        $Employee_Funds_account = AccountChart::create([
            'id' => 29,
            'account_group_id' => 5,
            'acode' => 2103,
            'title' => 'Employee Funds',
            'torise_debit' => 0,
        ]);

        $account = AccountChart::create([
            'id' => 30,
            'account_group_id' => 5,
            'acode' => 2104,
            'title' => 'securities',
            'torise_debit' => 0,
        ]);
        $account = AccountChart::create([
            'id' => 31,
            'account_group_id' => 5,
            'acode' => 2105,
            'title' => 'Society',
            'torise_debit' => 0,
        ]);
        ///////////////////////
        $account = AccountChart::create([
            'id' => 32,
            'account_group_id' => 6,
            'acode' => 2201,
            'title' => 'Accruid expences',
            'torise_debit' => 0,
        ]);
        $account = AccountChart::create([
            'id' => 33,
            'account_group_id' => 6,
            'acode' => 2202,
            'title' => 'short term loan',
            'torise_debit' => 0,
        ]);
        $account = AccountChart::create([
            'id' => 34,
            'account_group_id' => 6,
            'acode' => 2203,
            'title' => 'Payable to directors',
            'torise_debit' => 0,
        ]);
        $account = AccountChart::create([
            'id' => 35,
            'account_group_id' => 6,
            'acode' => 2204,
            'title' => 'Payable to employees',
            'torise_debit' => 0,
        ]);
        $account = AccountChart::create([
            'id' => 36,
            'account_group_id' => 6,
            'acode' => 2205,
            'title' => 'payable to campuses',
            'torise_debit' => 0,
        ]);
        $account = AccountChart::create([
            'id' => 37,
            'account_group_id' => 6,
            'acode' => 2206,
            'title' => 'payable to branches',
            'torise_debit' => 0,
        ]);
        $account = AccountChart::create([
            'id' => 38,
            'account_group_id' => 6,
            'acode' => 2207,
            'title' => 'payable to venders',
            'torise_debit' => 0,
        ]);
        $tax_payable = AccountChart::create([
            'id' => 39,
            'account_group_id' => 6,
            'acode' => 2208,
            'title' => 'Tax payable',
            'torise_debit' => 0,
        ]);
        $account = AccountChart::create([
            'id' => 40,
            'account_group_id' => 6,
            'acode' => 2209,
            'title' => 'payable to members',
            'torise_debit' => 0,
        ]);
        $account = AccountChart::create([
            'id' => 41,
            'account_group_id' => 6,
            'acode' => 2210,
            'title' => 'Profit and Lose',
            'torise_debit' => 0,
        ]);
        $account = AccountChart::create([
            'id' => 42,
            'account_group_id' => 6,
            'acode' => 2211,
            'title' => 'store purchases ventors',
            'torise_debit' => 0,
        ]);
        /////////////////////////////////
        $account = AccountChart::create([
            'id' => 43,
            'account_group_id' => 7,
            'acode' => 3101,
            'title' => 'store purchases ventors',
            'torise_debit' => 0,
        ]);
        $account = AccountChart::create([
            'id' => 44,
            'account_group_id' => 7,
            'acode' => 3102,
            'title' => 'store purchases ventors',
            'torise_debit' => 0,
        ]);
        //////////////
        $school_fee = AccountChart::create([
            'id' => 45,
            'account_group_id' => 8,
            'acode' => 4201,
            'title' => 'School fee',
            'torise_debit' => 0,
        ]);
        /////////////////////////
        $school_fee->sub_accounts()->create([
            'acode' => 42010001,
            'title' => 'Prospectus Fee',
            'torise_debit' => 0,
        ]);
        $school_fee->sub_accounts()->create([
            'acode' => 42010002,
            'title' => 'Registration Fee',
            'torise_debit' => 0,
        ]);
        $school_fee->sub_accounts()->create([
            'acode' => 42010003,
            'title' => 'Admission Fee',
            'torise_debit' => 0,
        ]);
        $school_fee->sub_accounts()->create([
            'acode' => 42010004,
            'title' => 'Monthly Fee',
            'torise_debit' => 0,
        ]);
        $school_fee->sub_accounts()->create([
            'acode' => 42010005,
            'title' => 'Annual Fund',
            'torise_debit' => 0,
        ]);
        $school_fee->sub_accounts()->create([
            'acode' => 42010006,
            'title' => 'Others Fine',
            'torise_debit' => 0,
        ]);
        $school_fee->sub_accounts()->create([
            'acode' => 42010007,
            'title' => 'Re-Admission Fee',
            'torise_debit' => 0,
        ]);
        $school_fee->sub_accounts()->create([
            'acode' => 42010008,
            'title' => 'Transport Fee',
            'torise_debit' => 0,
        ]);
        $school_fee->sub_accounts()->create([
            'acode' => 42010009,
            'title' => 'Other Fee',
            'torise_debit' => 0,
        ]);
        ////////////////////////////////
        $hostel_fee = AccountChart::create([
            'id' => 46,
            'account_group_id' => 8,
            'acode' => 4203,
            'title' => 'hostel Dues',
            'torise_debit' => 0,
        ]);
        $hostel_fee->sub_accounts()->create([
            'acode' => 42030001,
            'title' => 'Hostel Admission Fee',
            'torise_debit' => 0,
        ]);
        $hostel_fee->sub_accounts()->create([
            'acode' => 42030002,
            'title' => 'Hostel Monthly Fee',
            'torise_debit' => 0,
        ]);

        //////////////////
        $account = AccountChart::create([
            'id' => 47,
            'account_group_id' => 9,
            'acode' => 4301,
            'title' => 'non operational Receipts',
            'torise_debit' => 0,
        ]);
        $misscellaneous = AccountChart::create([
            'id' => 48,
            'account_group_id' => 9,
            'acode' => 4302,
            'title' => 'misscellaneous',
            'torise_debit' => 0,
        ]);
        //////////////
        $Salaries_allowance_benefits = AccountChart::create([
            'id' => 49,
            'account_group_id' => 10,
            'acode' => 5101,
            'title' => 'Salaries, allowance and benefits',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010001,
            'title' => 'Basic Pay',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010002,
            'title' => 'Hifz Allowance',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010003,
            'title' => 'Hostel Allowance',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010004,
            'title' => 'College Allowance',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010005,
            'title' => 'Additional Allowance',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010006,
            'title' => 'Increment Allowance',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010007,
            'title' => 'Second Shift Allowance',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010008,
            'title' => 'Qualification Allowance',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010009,
            'title' => 'Other Payment',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010010,
            'title' => 'Hod Allowance',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010011,
            'title' => 'Science Allowance',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010012,
            'title' => 'Substitute Staff',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010013,
            'title' => 'EOBI payments',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010014,
            'title' => 'Extra Period Allowance',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010015,
            'title' => 'Extra Coaching Allowance',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010016,
            'title' => 'BS Allowance',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010017,
            'title' => 'Masters Allowance',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010018,
            'title' => 'Apricition Allowance',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010019,
            'title' => 'UGS Allowance',
            'torise_debit' => 1,
        ]);
        $Salaries_allowance_benefits->sub_accounts()->create([
            'acode' => 51010020,
            'title' => 'Apricition Allowance',
            'torise_debit' => 1,
        ]);

        $account = AccountChart::create([
            'id' => 50,
            'account_group_id' => 10,
            'acode' => 5102,
            'title' => 'Utilities',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 51,
            'account_group_id' => 10,
            'acode' => 5103,
            'title' => 'Rent building',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 52,
            'account_group_id' => 10,
            'acode' => 5104,
            'title' => 'Printing and Stationary',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 53,
            'account_group_id' => 10,
            'acode' => 5105,
            'title' => 'postage and communication',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 54,
            'account_group_id' => 10,
            'acode' => 5106,
            'title' => 'Entertainment',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 55,
            'account_group_id' => 10,
            'acode' => 5107,
            'title' => 'newspaper and periodical',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 56,
            'account_group_id' => 10,
            'acode' => 5108,
            'title' => 'Medical',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 57,
            'account_group_id' => 10,
            'acode' => 5109,
            'title' => 'Awards & functions',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 58,
            'account_group_id' => 10,
            'acode' => 5110,
            'title' => 'computer and internet expence',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 59,
            'account_group_id' => 10,
            'acode' => 5111,
            'title' => 'laboratory expence',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 60,
            'account_group_id' => 10,
            'acode' => 5112,
            'title' => 'Students recreation Trips',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 61,
            'account_group_id' => 10,
            'acode' => 5113,
            'title' => 'Cleaning expence',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 62,
            'account_group_id' => 10,
            'acode' => 5114,
            'title' => 'Gardening and Ground expence',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 63,
            'account_group_id' => 10,
            'acode' => 5115,
            'title' => 'Travelling and conveyance',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 64,
            'account_group_id' => 10,
            'acode' => 5116,
            'title' => 'Fee and subscription',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 65,
            'account_group_id' => 10,
            'acode' => 5117,
            'title' => 'Examination',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 66,
            'account_group_id' => 10,
            'acode' => 5118,
            'title' => 'Sport days',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 67,
            'account_group_id' => 10,
            'acode' => 5119,
            'title' => 'Staff work shops',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 68,
            'account_group_id' => 10,
            'acode' => 5120,
            'title' => 'repair and maintenance ',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 69,
            'account_group_id' => 10,
            'acode' => 5121,
            'title' => 'fuel for generator',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 70,
            'account_group_id' => 10,
            'acode' => 5122,
            'title' => 'Consumable items',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 71,
            'account_group_id' => 10,
            'acode' => 5123,
            'title' => 'advertisement',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 72,
            'account_group_id' => 10,
            'acode' => 5124,
            'title' => 'misscellaneous expenses',
            'torise_debit' => 1,
        ]);
        ///////////////////////
        $account = AccountChart::create([
            'id' => 73,
            'account_group_id' => 11,
            'acode' => 5301,
            'title' => 'Salaries, allowance and benefits',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 74,
            'account_group_id' => 11,
            'acode' => 5302,
            'title' => 'Utilities',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 75,
            'account_group_id' => 11,
            'acode' => 5303,
            'title' => 'Food expence ',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 76,
            'account_group_id' => 11,
            'acode' => 5304,
            'title' => 'newspaper and periodical',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 77,
            'account_group_id' => 11,
            'acode' => 5305,
            'title' => 'Medical',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 78,
            'account_group_id' => 11,
            'acode' => 5306,
            'title' => 'Cleaning expence',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 79,
            'account_group_id' => 11,
            'acode' => 5307,
            'title' => 'Gardening and Ground expence',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 80,
            'account_group_id' => 11,
            'acode' => 5308,
            'title' => 'staff uniform expence',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 81,
            'account_group_id' => 11,
            'acode' => 5309,
            'title' => 'repair and maintenance building',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 82,
            'account_group_id' => 11,
            'acode' => 5310,
            'title' => 'repair and maintenance Furniture, Fittings',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 83,
            'account_group_id' => 11,
            'acode' => 5311,
            'title' => 'Consumable items',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 84,
            'account_group_id' => 11,
            'acode' => 5312,
            'title' => 'misscellaneous expenses',
            'torise_debit' => 1,
        ]);
        ///////////////////////
        $account = AccountChart::create([
            'id' => 85,
            'account_group_id' => 12,
            'acode' => 5501,
            'title' => 'operating expence',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 86,
            'account_group_id' => 12,
            'acode' => 5502,
            'title' => 'administrative expence',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 87,
            'account_group_id' => 12,
            'acode' => 5503,
            'title' => 'student expence',
            'torise_debit' => 1,
        ]);
        $account = AccountChart::create([
            'id' => 88,
            'account_group_id' => 12,
            'acode' => 5504,
            'title' => 'financial charges',
            'torise_debit' => 1,
        ]);
        $cash_sub_account = $cash_account->sub_accounts()->create([
            'acode' => 14020001,
            'title' => 'Cash',
            'torise_debit' => 1,
        ]);
        BankAccount::create([
            'sub_account_id' => $cash_sub_account->id,
            'bank_account_category_id' => 1,
            'bank_name' => 'Cash In Hand',
            'bank_branch' => 'Account Office',
            'account_title' => 'Cash',
            'account_number' => '111111111',
            'account_head' => '14020001',
        ]);

        $Employee_Funds_account->sub_accounts()->create([
            'acode' => 21030001,
            'title' => 'General Provident Fund',
            'torise_debit' => 0,
        ]);
        $Employee_Funds_account->sub_accounts()->create([
            'acode' => 21030002,
            'title' => 'Welfare Fund',
            'torise_debit' => 0,
        ]);
        $Employee_Funds_account->sub_accounts()->create([
            'acode' => 21030003,
            'title' => 'Eobi Fund',
            'torise_debit' => 0,
        ]);
        $Employee_Funds_account->sub_accounts()->create([
            'acode' => 21030004,
            'title' => 'Welfare fund For Class 4',
            'torise_debit' => 0,
        ]);
        $Employee_Funds_account->sub_accounts()->create([
            'acode' => 21030005,
            'title' => 'Staff Sequrity Deduction',
            'torise_debit' => 0,
        ]);
        $Employee_Funds_account->sub_accounts()->create([
            'acode' => 21030006,
            'title' => 'other Deduction',
            'torise_debit' => 0,
        ]);
        $tax_payable->sub_accounts()->create([
            'acode' => 22080001,
            'title' => 'Income Tax Payable',
            'torise_debit' => 0,
        ]);
        $child_fee_sub_account = $misscellaneous->sub_accounts()->create([
            'acode' => 43020003,
            'title' => 'Staff Child Fee Deduction Account',
            'torise_debit' => $misscellaneous->torise_debit,
        ]);
        BankAccount::create([
            'sub_account_id' => $child_fee_sub_account->id,
            'bank_account_category_id' => 0,
            'bank_name' => 'staff child fee deduction',
            'bank_branch' => 'staff child fee dection',
            'account_title' => 'staff child fee deduction',
            'account_number' => '100000000',
            'account_head' => '43020003',
        ]);
    }
}
