<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Accounts\BaseAccountResource;
use App\Models\AccountChart;
use App\Models\AccountGroup;
use App\Models\BaseAccount;
use App\Models\Campus;
use App\Models\GeneralLedger;
use App\Models\Session;
use App\Models\SubAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BaseAccountController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $baseAccount = BaseAccount::get();
        $baseAccount->load('account_groups.account_charts.sub_accounts');

        return $this->sendResponse(BaseAccountResource::collection($baseAccount), [], 200);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function balanceSheet(Request $request)
    {
        $validator = $this->validateReport($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $baseAccounts = BaseAccount::with('account_groups.account_charts')->get();

        $GL = GeneralLedger::where('session_id', $request->year_id)
         ->where(function ($query) use ($request) {
             return $request->campus_id != null ? $query->where('campus_id', $request->campus_id) : '';
         })->get();

        $report = [];

        foreach ($baseAccounts as $key1 => $baseAccount) {
            array_push($report, [
                'title' => $baseAccount->title,
                'acode' => $baseAccount->acode,
                'account_group' => [],
            ]);

            foreach ($baseAccount->account_groups as $key2 => $account_group) {
                array_push($report[$key1]['account_group'], [
                    'title' => $account_group->title,
                    'acode' => $account_group->acode,
                    'account_chart' => [],
                ]);

                foreach ($account_group->account_charts as $key3 => $account_chart) {
                    array_push($report[$key1]['account_group'][$key2]['account_chart'], [
                        'title' => $account_chart->title,
                        'acode' => $account_chart->acode,
                        'debit' => $GL->where('account_chart_id', $account_chart->id)->sum('debit'),
                        'credit' => $GL->where('account_chart_id', $account_chart->id)->sum('credit'),
                    ]);
                }
            }
        }
        // return $GL->sum('debit');
        $data = [
            'balance_sheet' => $report,
            'total_debit' => $GL->sum('debit'),
            'total_credit' => $GL->sum('credit'),
            'campus' => $request->campus_id ? Campus::find($request->campus_id)->name : 'All',
            'session' => $request->year_id ? Session::find($request->year_id)->year : 'All',
        ];

        return $this->sendResponse($data, []);
    }

    public function feesReport(Request $request)
    {
        $validator = $this->validateReport($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $account_chart_ids = AccountChart::whereIn('acode', [4201, 4203])->pluck('id')->toArray();

        $GL = GeneralLedger::whereIn('account_chart_id', $account_chart_ids)->where('session_id', $request->year_id)
        ->where(function ($query) use ($request) {
            return $request->campus_id != null ? $query->where('campus_id', $request->campus_id) : '';
        })->get();
        $subaccounts = SubAccount::whereIn('account_chart_id', $account_chart_ids)->get();
        // return $subaccounts;

        $report = [];

        foreach ($subaccounts as $key => $subaccount) {
            array_push($report, [
                'title' => $subaccount->title,
                'acode' => $subaccount->acode,
                'credit' => $GL->where('sub_account_id', $subaccount->id)->sum('credit'),
            ]);
        }
        $data = [
            'fees_report' => $report,
            'total_debit' => $GL->sum('debit'),
            'total_credit' => $GL->sum('credit'),
            'campus' => $request->campus_id ? Campus::find($request->campus_id)->name : 'All',
            'session' => $request->year_id ? Session::find($request->year_id)->year : 'All',
        ];

        return $this->sendResponse($data, []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function salaryReport(Request $request)
    {
        $validator = $this->validateReport($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $account_chart_ids = AccountChart::whereIn('acode', [5101])->pluck('id')->toArray();

        $GL = GeneralLedger::whereIn('account_chart_id', $account_chart_ids)->where('session_id', $request->year_id)
        ->where(function ($query) use ($request) {
            return $request->campus_id != null ? $query->where('campus_id', $request->campus_id) : '';
        })->get();
        $subaccounts = SubAccount::whereIn('account_chart_id', $account_chart_ids)->get();
        // return $subaccounts;

        $report = [];

        foreach ($subaccounts as $key => $subaccount) {
            array_push($report, [
                'title' => $subaccount->title,
                'acode' => $subaccount->acode,
                'debit' => $GL->where('sub_account_id', $subaccount->id)->sum('debit'),
            ]);
        }
        $data = [
            'fees_report' => $report,
            'total_debit' => $GL->sum('debit'),
            'total_credit' => $GL->sum('credit'),
            'campus' => $request->campus_id ? Campus::find($request->campus_id)->name : 'All',
            'session' => $request->year_id ? Session::find($request->year_id)->year : 'All',
        ];

        return $this->sendResponse($data, []);
    }

    public function expensReport(Request $request)
    {
        $validator = $this->validateReport($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $baseAccounts = BaseAccount::with('account_groups.account_charts')->where('acode', 5)->get();
        $base_account_ids = $baseAccounts->pluck('id');
        $accountGroups = AccountGroup::whereIn('base_account_id', $base_account_ids)->get();
        $account_group_ids = $accountGroups->pluck('id');
        $accountChart = AccountChart::whereIn('account_group_id', $account_group_ids)->get();
        $account_chart_ids = $accountChart->pluck('id');

        $GL = GeneralLedger::whereIn('account_chart_id', $account_chart_ids)->where('session_id', $request->year_id)
        ->where(function ($query) use ($request) {
            return $request->campus_id != null ? $query->where('campus_id', $request->campus_id) : '';
        })->get();
        // return $GL;

        $report = [];

        foreach ($baseAccounts as $key1 => $baseAccount) {
            array_push($report, [
                'title' => $baseAccount->title,
                'account_group' => [],
            ]);

            foreach ($baseAccount->account_groups as $key2 => $account_group) {
                array_push($report[$key1]['account_group'], [
                    'title' => $account_group->title,
                    'account_chart' => [],
                ]);

                foreach ($account_group->account_charts as $key3 => $account_chart) {
                    array_push($report[$key1]['account_group'][$key2]['account_chart'], [
                        'title' => $account_chart->title,
                        'acode' => $account_chart->acode,
                        'debit' => $GL->where('account_chart_id', $account_chart->id)->sum('debit'),
                    ]);
                }
            }
        }
        // return $GL->sum('debit');
        $data = [
            'balance_sheet' => $report,
            'total_debit' => $GL->sum('debit'),
            'total_credit' => $GL->sum('credit'),
            'campus' => $request->campus_id ? Campus::find($request->campus_id)->name : 'All',
            'session' => $request->year_id ? Session::find($request->year_id)->year : 'All',
        ];

        return $this->sendResponse($data, []);
    }

    private function validateReport(Request $request)
    {
        return Validator::make($request->all(), [
            'year_id' => 'required|integer|exists:sessions,id',
            'campus_id' => 'nullable|integer|exists:campuses,id',
        ]);
    }
}
