<?php

namespace App\Http\Controllers\Api\AccountReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Account\TrialBalanceRequest;
use App\Models\AccountGroup;
use App\Models\GeneralLedger;
use App\Models\SubAccount;
use App\Models\Voucher;

class TrialBalanceController extends BaseController
{
    // public function report(TrialBalanceRequest $request)
    // {
    //     $data = [];

    //     $account_groups = AccountGroup::get();

    //     $vocher_ids = Voucher::where('session_id',$request->year_id)
    //         ->when($request->date, function ($query) use ($request) {
    //             return $query->whereDate('date','<=',$request->date);
    //         })->pluck('id')->unique()->toArray();

    //     foreach ($account_groups as $account_group)
    //     {
    //         $account_charts = $account_group->account_charts;

    //         $account_group_data = [
    //             'account_group_code' => $account_group->acode,
    //             'account_group' => $account_group->title,
    //             'account_chart' => []
    //         ];

    //         foreach ($account_charts as $account_chart)
    //         {

    //             $account_chart_data = [
    //                 'account_chart_code' => $account_chart->acode,
    //                 'account_chart' => $account_chart->title,
    //                 'sub_account' => [],
    //             ];

    //             $sub_accounts = $account_chart->sub_accounts;

    //             foreach ($sub_accounts as $sub_account)
    //             {
    //                 $final_credit = 0;
    //                 $final_debit = 0;

    //                 $credit = GeneralLedger::where('sub_account_id',$sub_account->id)
    //                         ->whereIn('voucher_id',$vocher_ids)
    //                         ->sum('credit');

    //                 $debit = GeneralLedger::where('sub_account_id',$sub_account->id)
    //                         ->whereIn('voucher_id',$vocher_ids)
    //                         ->sum('debit');

    //                 $gl = GeneralLedger::where('sub_account_id',$sub_account->id)
    //                     ->whereIn('voucher_id',$vocher_ids)
    //                     ->get();

    //                 if ($credit-$debit > 0){
    //                     $final_credit = $credit-$debit;
    //                 }

    //                 if ($debit-$credit > 0){
    //                     $final_debit = $debit-$credit;
    //                 }

    //                 if ($final_credit == 0 && $final_debit == 0)
    //                     continue;

    //                 $sub_account_data = [
    //                     'sub_account_code' => $sub_account->acode,
    //                     'sub_account' => $sub_account->title,
    //                     'credit' => $final_credit,
    //                     'debit' => $final_debit,
    //                     'general_ledgers' => $gl,
    //                 ];

    //                 $account_chart_data['sub_account'][] = $sub_account_data;
    //             }

    //             $account_group_data['account_chart'][] = $account_chart_data;
    //         }

    //         $data[] = $account_group_data;
    //     }

    //     return $this->sendResponse($data, '',200);
    // }

    public function report(TrialBalanceRequest $request)
    {
        $data = [];

        $accountGroups = AccountGroup::with('account_charts.sub_accounts.general_ledgers')
            ->get();

        $voucherIds = Voucher::where('session_id', $request->year_id)
            ->when($request->date, function ($query) use ($request) {
                return $query->whereDate('date', '<=', $request->date);
            })
            ->pluck('id')
            ->unique()
            ->toArray();

        foreach ($accountGroups as $accountGroup) {
            $accountGroupData = [
                'account_group_code' => $accountGroup->acode,
                'account_group' => $accountGroup->title,
                'account_chart' => [],
            ];

            foreach ($accountGroup->account_charts as $accountChart) {
                $accountChartData = [
                    'account_chart_code' => $accountChart->acode,
                    'account_chart' => $accountChart->title,
                    'sub_account' => [],
                ];

                foreach ($accountChart->sub_accounts as $subAccount) {
                    $generalLedgers = $subAccount->general_ledgers->whereIn('voucher_id', $voucherIds);

                    $finalCredit = max(0, $generalLedgers->sum('credit') - $generalLedgers->sum('debit'));
                    $finalDebit = max(0, $generalLedgers->sum('debit') - $generalLedgers->sum('credit'));

                    if ($finalCredit == 0 && $finalDebit == 0) {
                        continue;
                    }

                    $subAccountData = [
                        'sub_account_code' => $subAccount->acode,
                        'sub_account' => $subAccount->title,
                        'credit' => $finalCredit,
                        'debit' => $finalDebit,
                        'general_ledgers' => $generalLedgers,
                    ];

                    $accountChartData['sub_account'][] = $subAccountData;
                }

                $accountGroupData['account_chart'][] = $accountChartData;
            }

            $data[] = $accountGroupData;
        }

        return $this->sendResponse($data, '', 200);
    }
}
