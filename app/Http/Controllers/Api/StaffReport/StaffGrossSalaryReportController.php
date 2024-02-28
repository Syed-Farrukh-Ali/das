<?php

namespace App\Http\Controllers\Api\StaffReport;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StaffPayDetailRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StaffGrossSalaryReportController extends BaseController
{
    public function staffGrossSalaryReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campus_id' => 'required|exists:campuses,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $campus_id = $request->campus_id;

        $employees = Employee::with([
            'bankAccount.sub_account',
            'designation',
            'salaryAllowance',
            'campus',
            'salaryDeduction',
            'GPFund', 'payScale',
            'employeeSalaries.voucher'
        ])->where('campus_id', $campus_id)->where('job_status_id', 1)->get();

        return $this->sendResponse(EmployeeResource::collection($employees), [], 200);
    }
}
