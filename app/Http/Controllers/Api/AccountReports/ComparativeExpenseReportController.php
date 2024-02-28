<?php

namespace App\Http\Controllers\Api\AccountReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Account\ComparativeExpenseReportRequest;
use App\Models\AccountGroup;
use App\Models\GeneralLedger;
use App\Models\SubAccount;
use Carbon\Carbon;

class ComparativeExpenseReportController extends BaseController
{
    public function report(ComparativeExpenseReportRequest $request)
    {
        $account_groups = AccountGroup::whereIn('id',[10,11,12])->get();

        $data = [];

        foreach ($account_groups as $account_group) {

            $account_group_data = [
                'account_group' => $account_group->title,
                'account_chart' => []
            ];

            $account_charts = $account_group->account_charts;

            foreach ($account_charts as $account_chart) {

                $account_chart_data = [
                    'account_chart' => $account_chart->title,
                    'month_data' => [],
                ];

                $months = GeneralLedger::where('account_chart_id', $account_chart->id)
                    ->when($request->campus_id, function ($query) use ($request) {
                        return $query->where('campus_id', $request->campus_id);
                    })
                    ->where('session_id', $request->year_id)
                    ->where('transaction_at','!=',null)
                    ->pluck('transaction_at')
                    ->map(function($timestamp) {
                        return Carbon::parse($timestamp)->month;
                    })
                    ->unique()
                    ->toArray();

                foreach ($months as $month)
                {

                    $created_at = GeneralLedger::where('account_chart_id', $account_chart->id)
                        ->when($request->campus_id, function ($query) use ($request) {
                            return $query->where('campus_id', $request->campus_id);
                        })
                        ->where('session_id', $request->year_id)
                        ->whereMonth('transaction_at',$month)
                        ->get(['transaction_at'])->first();

                    $year = Carbon::parse($created_at->created_at)->year;
                    $month_name = Carbon::createFromDate(null, $month, null)->format('F');

                    $month_data = [
                        'month_year' => $month_name.'-'.$year,
                        'sub_account' => [],
                    ];

                    $sub_accounts = SubAccount::where('account_chart_id', $account_chart->id)->get();

                    foreach ($sub_accounts as $sub_account) {

                        $debit = GeneralLedger::where('sub_account_id', $sub_account->id)
                            ->when($request->campus_id, function ($query) use ($request) {
                                return $query->where('campus_id', $request->campus_id);
                            })
                            ->where('session_id', $request->year_id)
                            ->whereMonth('transaction_at',$month)
                            ->sum('debit');

                        if ($debit == 0) {
                            continue;
                        }

                        $sub_account_data = [
                            'sub_account' => $sub_account->title,
                            'debit' => $debit,
                        ];

                        $month_data['sub_account'][] = $sub_account_data;
                    }

                    $account_chart_data['month_data'][] = $month_data;
                }

                $account_group_data['account_chart'][] = $account_chart_data;
            }

            $data[] = $account_group_data;
        }

        return $this->sendResponse($data, '',200);
    }
}
