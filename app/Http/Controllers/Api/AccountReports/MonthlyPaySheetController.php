<?php

namespace App\Http\Controllers\Api\AccountReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Account\MonthlyPaySheetRequest;
use App\Http\Requests\Account\PaySheetDetailsRequest;
use App\Http\Resources\SalaryResource;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use Carbon\Carbon;

class MonthlyPaySheetController extends BaseController
{
    public function index(MonthlyPaySheetRequest $request)
    {
        $date = Carbon::parse($request->date);

        $employee_salaries = EmployeeSalary::whereMonth('salary_month', $date)
            ->where('status', '!=', 0)
            ->when($request->cheque_no, function ($query) use ($request) {
                return $query->where('cheque_number', $request->cheque_no);
            })
            ->when($request->campus_id, function ($query) use ($request) {
                return $query->where('campus_id', $request->campus_id);
            })
            ->when($request->bank_id, function ($query) use ($request) {
                return $query->where('bank_account_id', $request->bank_id);
            })->get(['net_pay', 'account_no', 'employee_id', 'cheque_number']);

        $data = [];

        foreach ($employee_salaries as $employee_salary) {
            $data[] = [
                'emp_code' => $employee_salary->employee->emp_code,
                'full_name' => $employee_salary->employee->full_name,
                'net_pay' => $employee_salary->net_pay,
                'account_no' => $employee_salary->account_no,
                'cheque_number' => $employee_salary->cheque_number ?? '',
            ];
        }

        return $this->sendResponse($data, '', 200);
    }

    public function payDetails(PaySheetDetailsRequest $request)
    {

        $employee_ids = Employee::where('campus_id', $request->campus_id)
            // ->when($request->designation_id != 0, fn ($query) => $query->where('education_type', $request->education_type))
            ->when($request->designation_id, function ($query) use ($request) {
                return $query->where('designation_id', $request->designation_id);
            })
            ->pluck('id')
            ->toArray();

        $date = Carbon::parse($request->date);

        if ($request->bank_id == 1) {
            $bank_id = 1;
        } else {
            $bank_id = 0;
        }

        $employee_salaries = EmployeeSalary::with(['employee.GPFund', 'employee.payScale', 'voucher'])
            ->whereMonth('salary_month', $date)
            ->where('status', '!=', 0)
            ->whereIn('employee_id', $employee_ids)
            ->when($bank_id, function ($query) use ($bank_id) {
                return $query->where('bank_account_id', $bank_id);
            })
            ->get();

        return $this->sendResponse(SalaryResource::collection($employee_salaries), '', 200);
    }
}
