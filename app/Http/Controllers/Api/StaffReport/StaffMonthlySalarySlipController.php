<?php

namespace App\Http\Controllers\Api\StaffReport;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StaffReport\StaffMonthlySalarySlipRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Carbon\Carbon;

class StaffMonthlySalarySlipController extends BaseController
{
    public function index(StaffMonthlySalarySlipRequest $request)
    {
        $date = Carbon::parse($request->date);
        $month = $date->month;

        if ($request->campus_id){
            $employees = Employee::with([
                'campus',
                'designation',
                'bankAccount',
                'loans',
                'payScale',
                'GPFund',
                'salaryDeduction',
                'salaryAllowance',
                'employeeSalaries' => function ($query) use ($month)
                {
                    return $query
                        ->with('voucher')
                        ->whereMonth('salary_month',$month)
                        ->where('voucher_id','!=',null);
                }])
                ->where('campus_id',$request->campus_id)
                ->get();

            foreach ($employees as $employee) {
                $employee_loan = $employee->loans()->first();

                $remaining_amount = null;

                if ($employee_loan)
                {
                    $credit = $employee_loan->subAccount
                        ->general_ledgers
                        ->sum('credit');

                    $debit = $employee_loan->subAccount
                        ->general_ledgers
                        ->sum('debit');

                    $remaining_amount = $credit-$debit;
                }

                $employee->remaining_loan_amount =  $remaining_amount;
            }

            return $this->sendResponse($employees, '',200);
        }elseif($request->employee_code){
            $employee = Employee::with([
                'campus',
                'designation',
                'bankAccount',
                'loans',
                'payScale',
                'GPFund',
                'salaryDeduction',
                'salaryAllowance',
                'employeeSalaries' => function ($query) use ($month)
                {
                    return $query
                        ->with('voucher')
                        ->whereMonth('salary_month',$month)
                        ->where('voucher_id','!=',null);
                }])
                ->where('emp_code',$request->employee_code)
                ->first();

            $loan = $employee->loans()->first();

            $remaining_amount = null;

            if ($loan)
            {
                $credit = $loan->subAccount
                    ->general_ledgers
                    ->sum('credit');

                $debit = $loan->subAccount
                    ->general_ledgers
                    ->sum('debit');

                $remaining_amount = $credit-$debit;
            }

            $employee->remaining_loan_amount =  $remaining_amount;

            return $this->sendResponse($employee, '',200);
        }else{
            return $this->sendError('campus_id or employee_code is required', [],422);
        }
    }
}
