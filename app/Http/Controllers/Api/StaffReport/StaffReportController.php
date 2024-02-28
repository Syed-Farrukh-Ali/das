<?php

namespace App\Http\Controllers\Api\StaffReport;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\SalaryResource;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StaffReportController extends BaseController
{
    public function monthlyPaySummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'monthly_summary' => 'nullable|boolean',
            'cash_payment_summary' => 'nullable|boolean',
            'campus_wise_summary' => 'nullable|boolean',
            'bank_wise_summary' => 'nullable|boolean',
            'campus_id' => 'nullable|exists:campuses,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'salary_month' => ['nullable', 'date_format:Y-m-d',
                function ($student, $salary_month, $fail) {
                    if (substr($salary_month, -2) != '01') {
                        $fail('Oops! something wrong with fee month');
                    }
                },
            ],
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $request->all();
    }

       public function bankPaySheet(Request $request)
       {
           $validator = Validator::make($request->all(), [
               'bank_account_id' => 'required|exists:bank_accounts,id',
               // 'payment_type' => ['required',Rule::in(['Bank','Cash'])],
               'campus_id' => 'nullable|exists:campuses,id',
               'designation_ids.*' => 'required|exists:designations,id',
               'status.*' => ['nullable', Rule::in([0, 1, 2])],

               'salary_month' => ['nullable',
                   function ($attribute, $salary_month, $fail) {
                       if (substr($salary_month, -2) != '01') {
                           $fail('Oops! something wrong with salary month');
                       }
                   },
               ],
           ]);
           if ($validator->fails()) {
               return $this->sendError($validator->errors(), [], 422);
           }
           $bank_account_id = $request->bank_account_id;
           $campus_id = $request->campus_id;
           $designation_ids = $request->designation_ids;
           $salary_month = $request->salary_month;
           $employee_ids = Employee::where('bank_account_id', $bank_account_id)
           // ->where('payment_type',$request->payment_type)
           ->where(function ($query) use ($campus_id) {
               return $campus_id != null ? $query->where('campus_id', $campus_id) : '';
           })
           ->where(function ($query) use ($designation_ids) {
               return $designation_ids != null ? $query->whereIn('designation_id', $designation_ids) : '';
           })->get()->pluck('id');
           $salaries = EmployeeSalary::whereIn('status', $request->status)->whereIn('employee_id', $employee_ids)
            ->where(function ($query) use ($salary_month) {
                return $salary_month != null ? $query->where('salary_month', $salary_month) : '';
            })
            ->where(function ($query) use ($bank_account_id) {
                return $bank_account_id != null ? $query->where('bank_account_id', $bank_account_id) : '';
            })->get();

           $salaries->load('employee');
           // return $this->sendResponse(SalaryResource::collection($salaries),[],200);
           $result = [
               'salaries' => SalaryResource::collection($salaries),
               'total_net_salary' => $salaries->sum('net_pay'),
               'total_gross_salary' => $salaries->sum('gross_salary'),
           ];

           return $this->sendResponse($result, []);
       }

       public function empSalaryDetial(Request $request)
       {
           $validator = Validator::make($request->all(), [
               'emp_code' => 'required',
               'rang_date' => 'nullable|date|date_format:Y-m-d',
           ]);
           if ($validator->fails()) {
               return $this->sendError($validator->errors(), []);
           }
           $emp = Employee::where('emp_code', $request->emp_code)->first();
           $salaries = $emp->employeeSalaries()->where('salary_month', '>=', $request->rang_date)->get();
           $total_gross = $salaries->sum('gross_salary');
           $total_net_pay = $salaries->sum('net_pay');
           $salaries->load('employee');
           $data = [
               'salaries' => SalaryResource::collection($salaries),
               'total_gross' => $total_gross,
               'total_net_pay' => $total_net_pay,
           ];

           return $this->sendResponse($data, []);
       }

       public function employeePaySlip(Request $request)
       {
           $validator = Validator::make($request->all(), [
               'emp_code' => 'required',
               'salary_month' => ['nullable',
                   function ($attribute, $salary_month, $fail) {
                       if (substr($salary_month, -2) != '01') {
                           $fail('Oops! something wrong with salary month');
                       }
                   },
               ],
           ]);
           if ($validator->fails()) {
               return $this->sendError($validator->errors(), []);
           }
           $emp = Employee::where('emp_code', $request->emp_code)->first();
           $salary = $emp->employeeSalaries()->where('salary_month', $request->salary_month)->first();
           $salary->load('employee', 'bank_account');

           return $this->sendResponse(new SalaryResource($salary), []);
       }

       public function empGrossSalary(Request $request)
       {
           $validator = Validator::make($request->all(), [
               'campus_id' => 'required|exists:campuses,id',
               'job_status_id' => 'required|exists:job_statuses,id',

           ]);
           if ($validator->fails()) {
               return $this->sendError($validator->errors(), []);
           }

           $employees = Employee::where([
               'campus_id' => $request->campus_id,
               'job_status_id' => $request->job_status_id,
           ])->get();
           $employees->load('designation', 'payScale', 'salaryAllowance', 'designation');

           return $this->sendResponse(EmployeeResource::collection($employees), []);
       }
}
