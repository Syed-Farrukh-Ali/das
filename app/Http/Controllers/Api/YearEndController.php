<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountChart;
use App\Models\AccountGroup;
use App\Models\GeneralLedger;
use App\Models\Session;
use App\Models\SubAccount;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class YearEndController extends BaseController
{
    public function closingYearView(Request $req)
    {
        $year_id = $req->year_id;

        $chart_id = [];
        $acgroups = AccountGroup::whereBetween('base_account_id', [1, 3])->get();
        foreach ($acgroups as $ac) {
            $charts = AccountChart::where('account_group_id', $ac->id)->get();
            foreach ($charts as $account_charts) {
                $chart_id[] = SubAccount::where('account_chart_id', $account_charts->id)->pluck('id')->toArray();
            }
        }
        $flattened_chart_id = array_merge(...$chart_id);

        $results = GeneralLedger::with('sub_account')
            ->select('sub_account_id', DB::raw('SUM(credit) as cr'), DB::raw('SUM(debit) as dr'))
            ->where('session_id', $year_id)
            ->whereIn('sub_account_id', $flattened_chart_id)
            ->groupBy('sub_account_id')
            ->get();

        return $results;
    }
    public function sessionClosed(Request $req)
    {
        DB::beginTransaction();
        try {
        $date = date('Y-m-d');
        $Voucher = new Voucher;
        $Voucher->date = $date;
        $Voucher->voucher_type_id = "5";
        $Voucher->voucher_no = "JV-1";
        $Voucher->total_debit = $req->drval;
        $Voucher->total_credit = $req->crval;
        $Voucher->session_id = $req->session;
        $Voucher->resolved = "1";
        $Voucher->save();
        $voucher_id = $Voucher->id;
        $year_data = $req->input('year_data');
        foreach ($year_data as $yd) {
            echo $yd['account_chart_id'];
            echo $yd['sub_account_id'];
        }
        foreach ($year_data as $yd) {
            // voucher_id,sub_account_id,account_chart_id,session_id,campus_id,transaction_at,remarks,debit,credit
            $GeneralLedger = new GeneralLedger;
            $GeneralLedger->transaction_at = $date;
            $GeneralLedger->voucher_id = $voucher_id;
            $GeneralLedger->sub_account_id = $yd['sub_account_id'];
            $GeneralLedger->account_chart_id = $yd['account_chart_id'];
            $GeneralLedger->campus_id = $yd['campus_id'];
            $GeneralLedger->session_id = $yd['session'];
            $GeneralLedger->remarks = $yd['nuration'];
            $GeneralLedger->debit = $yd['dr'];
            $GeneralLedger->credit = $yd['cr'];
            $GeneralLedger->save();
        }
        Session::where('id', $req->session)->update(['active_financial_year' => '1']);
        Session::where('id', $req->old_session)->update(['active_financial_year' => '0']);
            //$sessions=Session::where('id',$req->session)->get();
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }

        DB::commit();

    }
}
