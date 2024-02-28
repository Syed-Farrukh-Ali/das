<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\GeneralLedger;
use Illuminate\Http\Request;

class FirmWiseReportController extends BaseController
{
    public function firmView(Request $req)
    {
        $year_id = $req->year_id;
        $bank_id = $req->bank_id;
        $sub_account_ids = BankAccount::where('bank_account_category_id', $bank_id)->pluck('sub_account_id')->toArray();
        //$chart_id=[];
        //  $acgroups=AccountGroup::whereBetween('base_account_id', [1,3])->get();
        //  foreach($acgroups as $ac){
        //      $charts = AccountChart::where('account_group_id',$ac->id)->get();
        // foreach($charts as $account_charts){
        //    $chart_id[]=SubAccount::where('account_chart_id',$account_charts->id)->pluck('id')->toArray();
        // }
        // }
        // $flattened_chart_id = array_merge(...$subacc);

        $result = GeneralLedger::with('sub_account')
            ->select('sub_account_id', 'remarks', 'credit as cr', 'debit as dr')
            ->where('session_id', $year_id)
            ->whereIn('sub_account_id', $sub_account_ids)
            ->get();

        return $result;
    }
}
