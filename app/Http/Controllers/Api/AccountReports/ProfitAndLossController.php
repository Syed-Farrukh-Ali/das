<?php

namespace App\Http\Controllers\Api\AccountReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Account\ProfitAndLossRequest;
use App\Models\AccountGroup;
use App\Models\GeneralLedger;
use App\Models\SubAccount;

class ProfitAndLossController extends BaseController
{
    public function __invoke(ProfitAndLossRequest $request)
    {

        $data = [
            'revenue' => $this->accountGroupData(AccountGroup::whereIn('id',[8,9])->get(), $request),
            'expenditure' => $this->accountGroupData(AccountGroup::whereIn('id',[10,11,12])->get(), $request),
        ];

        return $this->sendResponse($data, '',200);
    }

    public function accountGroupData($account_groups, ProfitAndLossRequest $request)
    {
        $data = [];

        foreach ($account_groups as $account_group)
        {
            $account_charts = $account_group->account_charts;

            $account_group_data = [
                'account_group_code' => $account_group->acode,
                'account_group' => $account_group->title,
                'account_chart' => []
            ];

            foreach ($account_charts as $account_chart)
            {

                $account_chart_data = [
                    'account_chart_code' => $account_chart->acode,
                    'account_chart' => $account_chart->title,
                    'sub_account' => [],
                ];

                $sub_accounts = SubAccount::where('account_chart_id',$account_chart->id)->get();

                foreach ($sub_accounts as $sub_account)
                {

                    $credit = GeneralLedger::where('sub_account_id',$sub_account->id)->where('campus_id',$request->campus_id)
                        ->where('session_id',$request->year_id)
                        ->when($request->start_date, function ($query) use ($request) {
                            return $query->whereDate('transaction_at','>=',$request->start_date);
                        })
                        ->when($request->end_date, function ($query) use ($request) {
                            return $query->whereDate('transaction_at','<=',$request->end_date);
                        })->sum('credit');

                    $debit = GeneralLedger::where('sub_account_id',$sub_account->id)->where('campus_id',$request->campus_id)
                        ->where('session_id',$request->year_id)
                        ->when($request->start_date, function ($query) use ($request) {
                            return $query->whereDate('transaction_at','>=',$request->start_date);
                        })
                        ->when($request->end_date, function ($query) use ($request) {
                            return $query->whereDate('transaction_at','<=',$request->end_date);
                        })->sum('debit');

                    if ($credit == 0 && $debit == 0)
                    {
                        continue;
                    }

                    $sub_account_data = [
                        'sub_account_code' => $sub_account->acode,
                        'sub_account' => $sub_account->title,
                        'credit' => $credit,
                        'debit' => $debit,
                    ];

                    $account_chart_data['sub_account'][] = $sub_account_data;
                }

                $account_group_data['account_chart'][] = $account_chart_data;
            }

            $data[] = $account_group_data;
        }

        return $data;
    }
}
