<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\GeneralLedger;
use App\Models\BaseAccount;
use App\Models\AccountGroup;
use App\Models\AccountChart;
use App\Models\SubAccount;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GPFundReturn extends BaseController
{
    public function EmployeeSearch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emp_id'  => 'required|exists:employees,emp_code',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $employee_id = $request->emp_id;
        $employee_data = employee::with('GPFund')->with('employeeSalaries1')->where('emp_code', $employee_id)->get();
        return $this->sendResponse($employee_data, '', 200);
    }
    public function GenrateGPFundReturn(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'employee_id'  => 'required|exists:employees,id',
            'campus_id'  => 'required|exists:employees,campus_id',
            'session_id'  => 'required',
            'bank_account_id'  => 'required|exists:employees,bank_account_id',
            'cheque_number'  => 'required',
            'salary_month' => 'required',
            'gpf_return' => 'required',
            'account_no'  => 'required|exists:employees,account_no',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $employeesalary = new EmployeeSalary;
        $employeesalary->employee_id = $req->employee_id;
        $employeesalary->campus_id = $req->campus_id;
        $employeesalary->session_id = $req->session_id;
        $employeesalary->bank_account_id = $req->bank_account_id;
        $employeesalary->cheque_number = $req->cheque_number;
        $employeesalary->salary_month = $req->salary_month;
        $employeesalary->account_no = $req->account_no;
        $employeesalary->net_pay = $req->gpf_return;
        $employeesalary->basic_pay = '0';
        $employeesalary->gross_salary = '0';
        $employeesalary->hifz = '0';
        $employeesalary->hostel = '0';
        $employeesalary->college = '0';
        $employeesalary->additional_allowance = '0';
        $employeesalary->increment = '0';
        $employeesalary->second_shift = '0';
        $employeesalary->ugs = '0';
        $employeesalary->other_allowance = '0';
        $employeesalary->hod = '0';
        $employeesalary->science = '0';
        $employeesalary->extra_period = '0';
        $employeesalary->extra_coaching = '0';
        $employeesalary->convance = '0';
        $employeesalary->eobi_payments = '0';
        $employeesalary->gpf_return = $req->gpf_return;
        $employeesalary->eobi = '0';
        $employeesalary->income_tax = '0';
        $employeesalary->insurance = '0';
        $employeesalary->van_charge = '0';
        $employeesalary->other_deduction = '0';
        $employeesalary->child_fee_deduction = '0';
        $employeesalary->gp_fund = '0';
        $employeesalary->welfare_fund = '0';
        $employeesalary->loan_refund = '0';
        $employeesalary->status = '1';
        $employeesalary->days = '0';
        $employeesalary->paid_at = '2023-10-10 00:00:00';
        $employeesalary->save();
        //return $employeesalary;
        // return $this->sendResponse($employeesalary, '', 200);
        if ($employeesalary) {
            return $this->sendResponse('GP Fund Paid Successfuly', 200);
        }
    }
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
        // $flattened_chart_id = array_merge(...$chart_id);
        //$chart_id_string = implode(',', $flattened_chart_id);

        //$query = "SELECT sub_account_id, SUM(credit) AS cr, SUM(debit) AS dr
        //    FROM general_ledgers
        //     WHERE session_id = 5 AND sub_account_id IN ($chart_id_string)
        //          AND general_ledgers.deleted_at IS NULL
        //     GROUP BY sub_account_id";

        //return DB::select($query);
        // return GeneralLedger::with('sub_account')->select(['sub_account_id',DB::raw("SUM(credit) as cr"),DB::raw("SUM(debit) as dr")])->where('session_id','5')->whereIn('sub_account_id',$chart_id)->groupBy('sub_account_id')->get();
        //return DB::statement("select sub_account_id, SUM(credit) as cr, SUM(debit) as dr from general_ledgers where session_id = 5 and sub_account_id in $chart_id_string and general_ledgers.deleted_at is null group by sub_account_id");

        // return BaseAccount::with('account_groups.account_charts.sub_accounts')->whereBetween('acode', [1, 3])->get();
        // return GeneralLedger::with('sub_account')->select(['sub_account_id', 'account_chart_id',DB::raw("SUM(credit) as cr"),DB::raw("SUM(debit) as dr")])->where('session_id','5')->groupBy(['sub_account_id', 'account_chart_id'])->get();
        //return GeneralLedger::select('sub_account_id', 'account_chart_id',DB::raw('SUM(credit) as cr'),DB::raw('SUM(debit) as dr'))->groupBy(['sub_account_id', 'account_chart_id'])->get();

        // return response()->json($data);
    }
}
