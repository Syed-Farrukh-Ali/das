<?php

namespace App\Http\Controllers\Api\AccountReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Account\DateWiseMonthlyExpenseRequest;
use App\Models\AccountGroup;
use App\Models\GeneralLedger;
use Carbon\Carbon;

class DateWiseMonthlyExpenseSummaryController extends BaseController
{
    public function report(DateWiseMonthlyExpenseRequest $request)
    {
        $month = Carbon::parse($request->date)->month;
        $year = Carbon::parse($request->date)->year;

        $date_wise_array = $this->getDatesOfMonth($year, $month);

        $account_groups = AccountGroup::whereIn('id',[10,11,12])->get();

        $data = [];

        foreach ($account_groups as $account_group)
        {
            $account_charts = $account_group->account_charts;

            $account_group_data = [
                'account_group' => $account_group->title,
                'account_chart' => [],
            ];

            foreach ($account_charts as $account_chart) {

                $account_chart_data = [
                    'account_chart' => $account_chart->title,
                    'sub_account' => [],
                ];

                foreach ($account_chart->sub_accounts as $sub_account)
                {

                    $sub_account_data = [
                        'sub_account' => $sub_account->title,
                        'date_wise_data' => [],
                    ];

                    foreach ($date_wise_array as $date)
                    {
                        $debit = GeneralLedger::where('sub_account_id', $sub_account->id)
                            ->when($request->campus_id, function ($query) use ($request) {
                                return $query->where('campus_id', $request->campus_id);
                            })
                            ->whereNotNull('transaction_at')
                            ->whereDate('transaction_at',$date)
                            ->where('session_id',$request->year_id)
                            ->sum('debit');

                        if ($debit == 0) {
                            continue;
                        }

                        $date_wise_data = [
                            'date' => $date,
                            'debit' => $debit,
                        ];

                        $sub_account_data['date_wise_data'][] = $date_wise_data;

                    }

                    $account_chart_data['sub_account'][] = $sub_account_data;
                }

                $account_group_data['account_chart'][] = $account_chart_data;
            }

            $data[] = $account_group_data;
        }

        return $this->sendResponse($data, '',200);
    }

    public function getDatesOfMonth($year, $month)
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        $dates = [];
        while ($startOfMonth->lte($endOfMonth)) {
            $dates[] = $startOfMonth->toDateString();
            $startOfMonth->addDay();
        }

        return $dates;
    }
}
