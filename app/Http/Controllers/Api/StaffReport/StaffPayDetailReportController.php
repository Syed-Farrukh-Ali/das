<?php

namespace App\Http\Controllers\Api\StaffReport;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StaffPayDetailRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\EmployeeSalary;

class StaffPayDetailReportController extends BaseController
{
    public function staffPayDetailReport(StaffPayDetailRequest $request)
    {
        $campus_id = _campusId();

        if ($campus_id){
            $employee_ids = EmployeeSalary::when($request->year_id, function($query) use($request) {
                $query->where('session_id', $request->year_id);
            })->where('campus_id',$campus_id)->pluck('employee_id')->toArray();

            $employees = Employee::with(['bankAccount',
                'designation',
                'salaryAllowance',
                'campus',
                'salaryDeduction',
                'GPFund','payScale',
                'employeeSalaries.bank_account',
                'employeeSalaries.voucher'])
                ->whereIn('id',$employee_ids)
                ->when($request->employee_code, function($query) use($request) {
                    $query->where('emp_code',$request->employee_code);
                })
                ->where('campus_id',$campus_id)
                ->get();

        }else{
            $employee_ids = EmployeeSalary::when($request->year_id, function($query) use($request) {
                $query->where('session_id', $request->year_id);
            })->pluck('employee_id')->toArray();

            $employees = Employee::with(['bankAccount.sub_account',
                'designation',
                'salaryAllowance',
                'salaryDeduction',
                'campus',
                'GPFund','payScale',
                'employeeSalaries.bank_account',
                'employeeSalaries.voucher'])
                ->whereIn('id',$employee_ids)
                ->when($request->employee_code, function($query) use($request)
                {
                    $query->where('emp_code',$request->employee_code);
                })->get();
        }

        return $this->sendResponse(EmployeeResource::collection($employees), [], 200);
    }
}
