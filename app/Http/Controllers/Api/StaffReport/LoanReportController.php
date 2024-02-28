<?php

namespace App\Http\Controllers\Api\StaffReport;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StaffLoanReportRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\GeneralLedger;
use App\Models\Loan;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class LoanReportController extends BaseController
{
    public function staffLoanReport(StaffLoanReportRequest $request)
    {

        $session_year = GeneralLedger::when($request->year_id, function ($query) use ($request) {
            return $query->where('session_id', $request->year_id);
        })->get(['session_id'])->first();

        if ($session_year) {

            $employee_ids = Loan::pluck('employee_id')->toArray();

            $employees = Employee::with(['bankAccount', 'designation', 'loans'])->whereIn('id', $employee_ids)->get();

            $loans = Loan::whereIn('employee_id', $employee_ids)->get();
            $employee_ = [];
            foreach ($employees as $employee) {
                $loan = $employee->loans()->first();

                $credit = $loan->subAccount
                    ->general_ledgers
                    ->when($request->year_id, function ($query) use ($request) {
                        return $query->where('session_id', $request->year_id);
                    })
                    ->sum('credit');

                $debit = $loan->subAccount
                    ->general_ledgers
                    ->when($request->year_id, function ($query) use ($request) {
                        return $query->where('session_id', $request->year_id);
                    })
                    ->sum('debit');
                $remaining_amount = $debit - $credit;

                //                $data = [ 'employee' => $employee ,'remaining_amount'=>$remaining_amount];
                $employee->remaining_amount =  $remaining_amount;

                if ($remaining_amount > 0)
                    $employee_[] = $employee;
            }

            return $this->sendResponse($employee_, '', 200);
        } else {
            return $this->sendResponse([], 'Record not found for this session', 200);
        }


        //        $employees = Employee::query()
        //            ->whereHas('loans', function ($query) {
        //                return $query->where('status', 1);
        //            })
        //            ->when($request->employee_code, function($query) use($request) {
        //                $query->where('emp_code',$request->employee_code);
        //            })
        //            ->with(['bankAccount','designation','loans' => function ($query) use ($request){
        //                return $query->where('status', 1)
        //                    ->join('sub_accounts', 'loans.sub_account_id', '=', 'sub_accounts.id')
        //                    ->join('general_ledgers', function ($join) use ($request){
        //                        $join->on( 'sub_accounts.id', '=', 'general_ledgers.sub_account_id')
        //                            ->when($request->year_id, function ($query) use ($request) {
        //                                return $query->where('session_id', $request->year_id);
        //                            });
        //                    })
        //                    ->select('loans.*', DB::raw('SUM(general_ledgers.credit - general_ledgers.debit) AS remaining_amount'))
        //                    ->groupBy('loans.id',`loans.employee_id`);
        //            }])
        //            ->get();
    }
}
