<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\EmployeeResource;
use App\Http\Resources\EmpSalaryDetailResource;
use App\Http\Resources\SalaryResource;
use App\Models\BankAccount;
use App\Models\Campus;
use App\Models\ChequePaySalary;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\FeeChallan;
use App\Models\FeeChallanDetail;
use App\Models\SalaryDeduction;
use App\Models\Student;
use App\Repository\EmployeeSalaryRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EmployeeSalaryController extends BaseController
{
    public function __construct(EmployeeSalaryRepository $employeeSalaryRepository)
    {
        $this->employeeSalaryRepository = $employeeSalaryRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function singleEmpSalary(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), [
            'is_returning_gpf' => ['nullable', Rule::In([0, 1])],
            'year_id' => ['required', 'exists:sessions,id'],
            'salary_month' => [
                'required',
                function ($attribute, $salary_month, $fail) {
                    if (substr($salary_month, -2) != '01') {
                        $fail('Oops! something wrong with salary month');
                    }
                },
            ],
            'job_status_id' => ['nullable', 'exists:job_statuses,id'],
            'gpf_return_amount' => ['nullable', 'integer'],
            'preview' => ['nullable', Rule::In([0, 1])],
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        if ($employee->job_status_id != 1) {
            return $this->sendError(['this employee is not in service'], [], 422);
        }

        $salary = $this->employeeSalaryRepository->singleEmpSalary($request, $employee);

        if ($salary) {
            return $this->sendResponse(new SalaryResource($salary), ['salary generated successfully']);
        }

        return $this->sendError([], $this->serverErrorMessage(), 500);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getEmpSalary(Employee $employee)
    {
        if ($employee->job_status_id != 1) {
            return $this->sendError(['this employee is not in service'], [], 422);
        }

        return $this->sendResponse(SalaryResource::collection($employee->employeeSalaries), []);
    }

    public function updateEmpSalary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:employee_salaries,id',
            'basic_pay' => 'required|integer',
            'hifz' => 'nullable|integer',
            'hostel' => 'nullable|integer',
            'college' => 'nullable|integer',
            'additional_allowance' => 'nullable|integer',
            'increment' => 'nullable|integer',
            'second_shift' => 'nullable|integer',
            'ugs' => 'nullable|integer',
            'other_allowance' => 'nullable|integer',
            'hod' => 'nullable|integer',
            'science' => 'nullable|integer',
            'extra_period' => 'nullable|integer',
            'extra_coaching' => 'nullable|integer',
            'convance' => 'nullable|integer',
            'eobi' => 'nullable|integer',
            'eobi_payment' => 'nullable|integer',
            'income_tax' => 'nullable|integer',
            'insurance' => 'nullable|integer',
            'van_charge' => 'nullable|integer',
            'other_deduction' => 'nullable|integer',
            'gp_fund' => 'nullable|integer',
            'gp_return' => 'nullable|integer',
            'child_fee_deduction' => 'nullable|integer',
            'welfare_fund' => 'nullable|integer',
            'days' => 'nullable|integer',
            'bank_account_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $employeeSalary = EmployeeSalary::find($request->id);
        if ($employeeSalary->status > 2) {
            return $this->sendError('unable to update a paid salary', []);
        }

        $employeeSalary = $this->employeeSalaryRepository->updateEmpSalary($request, $employeeSalary);

        if ($employeeSalary) {
            return $this->sendResponse(new SalaryResource($employeeSalary), ['salary updated successfully']);
        }

        return $this->sendError([], $this->serverErrorMessage(), 500);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function GetSalariesFilter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campus_id' => 'nullable|exists:campuses,id',
            'designation_ids.*' => 'nullable|exists:designations,id',
            'salary_month' => [
                'nullable',
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

        $employee_ids = Employee::when($request->designation_ids, fn ($query) => $query->whereIn('designation_id', $request->designation_ids))
            ->when($request->campus_id, fn ($query) => $query->where('campus_id', $request->campus_id))
            ->where('pay_scale_id', '!=', null)->pluck('id')->toArray();


        $salaries = EmployeeSalary::with('employee')->where('status', 0)->whereIn('employee_id', $employee_ids)
            ->when($request->salary_month, fn ($query) => $query->where('salary_month', $request->salary_month))
            ->latest()
            ->get();

        $result = [
            'salaries' => SalaryResource::collection($salaries),
            'total_net_salary' => $salaries->sum('net_pay'),
            'total_gross_salary' => $salaries->sum('gross_salary'),
        ];

        return $this->sendResponse($result, []);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\EmployeeSalary $employeeSalary
     * @return \Illuminate\Http\Response
     */
    // public function bulkSalaryGenerate(Request $request)
    // { // this salary generation is not using now, go to 177 line nmber
    //     $validator = Validator::make($request->all(), [
    //         'salary_month'     =>
    //          ['required',
    //             function ($attribute, $salary_month, $fail) {
    //                 if ( substr($salary_month, -2) != '01' )
    //                 {
    //                     $fail('Oops! something wrong with salary month');
    //                 }
    //             },
    //          ],
    //         ]);
    //         if ($validator->fails() )
    //         {
    //             return $this->sendError($validator->errors() , [],422);
    //         }

    //     $response = $this->employeeSalaryRepository->bulkSalaryGenerate($request);

    //     if($response)
    //     {
    //         return $this->sendResponse(
    //             [
    //               'empHaveSomeIssue' => EmpSalaryDetailResource::collection($response['empHaveSomeIssue']),
    //               'empAlreadyHaveGenerated' =>  EmpSalaryDetailResource::collection($response['empAlreadyHaveGenerated']),
    //               'salaries' =>  SalaryResource::collection($response['salaries']),
    //             ],

    //             ['skiped salaries due to error='.$response['skipedCount'],
    //             '.total employee ='.$response['empCount'],
    //             '.have already salary for this month ='.$response['alreadyHasSalary']
    //             ]);
    //     }

    //     return $this->sendError([],$this->serverErrorMessage(),500);

    // }

    public function getEmployeeList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campus_id' => ['required', Rule::in(Campus::all()->pluck('id')->toArray())],
            'designation_ids.*' => ['nullable', Rule::in(Designation::all()->pluck('id')->toArray())],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $response = $this->employeeSalaryRepository->getEmployeeList($request);
        if ($response) {
            return $this->sendResponse(EmployeeResource::collection($response), []);
        }

        return $this->sendError([], $this->serverErrorMessage(), 500);
    }

    public function bulkSalaryGenerateByList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => ['required', 'exists:sessions,id'],
            'employee_id.*' => ['integer', Rule::in(Employee::all()->pluck('id')->toArray())],
            'salary_month' => [
                'required',
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
        $response = $this->employeeSalaryRepository->bulkSalaryGenerateByList($request);

        if ($response) {
            return $this->sendResponse(
                [
                    'empAlreadyHaveGenerated' => EmpSalaryDetailResource::collection($response['empAlreadyHaveGenerated']),
                    'salaries' => SalaryResource::collection($response['salaries']),
                ],

                [
                    'salary generated',
                ]
            );
        }

        return $this->sendError([], $this->serverErrorMessage(), 500);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\EmployeeSalary $employeeSalary
     * @return \Illuminate\Http\Response
     */
    public function salariesBankWise(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'campus_id' => 'nullable|exists:campuses,id',
            'designation_id.*' => 'nullable|exists:designations,id',

            'salary_month' => [
                'nullable',
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
        $campus_id = $request->campus_id;
        $salary_month = $request->salary_month;

        $employee_ids = Employee::where('bank_account_id', $request->bank_account_id)
            ->when(count($request->designation_id), fn ($query) => $query->whereIn('designation_id', $request->designation_id))
            ->where(function ($query) use ($campus_id) {
                return $campus_id != null ? $query->where('campus_id', $campus_id) : '';
            })
            ->get()->pluck('id');

        $salaries = EmployeeSalary::where('status', 0)->whereIn('employee_id', $employee_ids)
            ->where(function ($query) use ($salary_month) {
                return $salary_month != null ? $query->where('salary_month', $salary_month) : '';
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

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\EmployeeSalary $employeeSalary
     * @return \Illuminate\Http\Response
     */
    public function paySalaries(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'salary_id.*' => 'required|exists:employee_salaries,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'cheque_number' => 'nullable|string|max:20',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $chequeNo = null;

        $employees_to_update = EmployeeSalary::whereIn('id', $request->salary_id)
            ->where('status', '=', 0)
            ->get();

        if ($employees_to_update->isEmpty()) {
            return $this->sendResponse([], 'All selected employees have already been paid.', 200);
        } else {

            DB::beginTransaction();
            try {


                $employees_salaries = EmployeeSalary::whereIn('id', $request->salary_id)->get();

                foreach ($employees_salaries as $salary) {

                    $employee = Employee::find($salary->employee_id);

                    $employee->GPFund->increment('collected_amount', $salary->gp_fund);

                    $salary_days = $employee->salery_days;

                    if ($salary_days > 30 || $salary_days < 30) {
                        $employee->update([
                            'salery_days' => 30,
                        ]);
                    }

                    // Code for auto clear other deduction
                    if ($employee->auto_clear_deduction == 1) {
                        $employee->update(['auto_clear_deduction' => 0]);

                        SalaryDeduction::where('employee_id', $employee->id)
                            ->update([
                                'other' => 0,
                            ]);
                    }

                    if ($employee->students->isNotEmpty()) {

                        $childs = $employee->students()->where('status', '=', '2')->get();
                        $std_ids = $childs->pluck('id');

                        $student_fee_month = Carbon::parse($salary->salary_month)->addMonth();


                        $feechallans = FeeChallan::with([
                            'feeChallanDetails' => function ($query) use ($student_fee_month) {
                                $query->whereDate('fee_month', '=', $student_fee_month);
                            }
                        ])
                            ->where(['status' => 0])
                            ->whereIn('student_id', $std_ids)
                            ->get();


                        $total_fee_details_amount = 0;

                        foreach ($feechallans as $feechallan) {
                            $total_fee_details_amount += $feechallan->feeChallanDetails->sum('amount');
                        }
                        // if employee deducted amount doesnt matched the avaiilable challans
                        if ($salary->child_fee_deduction != $total_fee_details_amount) {
                            // DB::rollBack();
                            // return $this->sendResponse('', 'Emp Code ' . $employee->emp_code . ' Child Fee Not Matched '
                            //     . ' Deducted Fee: ' . $salary->child_fee_deduction .  ' UnPaid Fee: ' . $total_fee_details_amount, 422);
                            $salary->child_fee_deduction = $total_fee_details_amount;
                        }

                        foreach ($feechallans as $challan) {
                            if (count($challan->feeChallanDetails) > 0)
                                $challan->update(['status' => 1, 'paid' => $challan->payable, 'feed_at' => Carbon::now(), 'received_date' => date('Y-m-d'), 'bank_account_id' => _childFeeBankAccount()->id ?? 'child fee deduction bank account id']);
                        }

                        foreach (Student::find($feechallans->pluck('student_id')) as $student) {
                            if ($student->status == 3) {
                                _studentAdmission($student, date('Y-m-d'));
                            }
                        }
                    }

                    $gpf_return_value = $salary->gpf_return;
                    $employee->GPFund->decrement('collected_amount', $gpf_return_value);
                }

                //            if ($request->cheque_number) {
                //                $cheque = ChequePaySalary::firstOrCreate([
                //                    'cheque_number' => $request->cheque_number,
                //                    'bank_account_id' => $request->bank_account_id,
                //                    'date' => Carbon::now(),
                //                ]);
                //                $chequeNo = $cheque->id;
                //            }
                EmployeeSalary::whereIn('id', $request->salary_id)->update(['status' => 1, 'cheque_number' => $request->cheque_number]);
                $salaries = EmployeeSalary::with('employee')->whereIn('id', $request->salary_id)->get();
                $data = [

                    'bank' => BankAccount::find($request->bank_account_id),
                    'cheque_number' => $request->cheque_number,
                    'salaries' => SalaryResource::collection($salaries),
                    'total_net_salary' => $salaries->sum('net_pay'),
                    'total_gross_salary' => $salaries->sum('gross_salary'),
                ];
            } catch (\Throwable $e) {
                DB::rollBack();

                return false;
            }

            DB::commit();
            return $this->sendResponse($data, 'please print the list of salaries', 200);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\EmployeeSalary $employeeSalary
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeSalary $employeeSalary)
    {
        //
    }

    public function updateGPFund(Request $request, Employee $employee)
    {
        $employee->GPFund->update(['collected_amount' => $request->collected_amount]);

        return $this->sendResponse(new EmployeeResource($employee), 'gp fund updated', 200);
    }
}
