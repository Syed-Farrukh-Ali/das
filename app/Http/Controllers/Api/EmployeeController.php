<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Employee\EmployeesFilterByNameRequest;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\EmpSalaryDetailResource;
use App\Models\Campus;
use App\Models\Employee;
use App\Repository\EmployeeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EmployeeController extends BaseController
{
    protected $employeeRepository;

    public function __construct(EmployeeRepository $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Campus $campus)
    {
        return $this->sendResponse($this->employeeRepository->index($campus), []);
    }

    public function allEmpForStatus(Request $request)
    {
        return $this->sendResponse($this->employeeRepository->allEmpForStatus($request), []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validateEmployee($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->employeeRepository->store($request), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        return $this->sendResponse($this->employeeRepository->show($employee), []);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee) //only for registered employee only
    {
        $validator = $this->validateEmployee($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->employeeRepository->update($request, $employee), []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        if ($employee->status != 1) {
            return $this->sendError('you can delete only registered employees', [], 422);
        }

        return $this->sendResponse($this->employeeRepository->destroy($employee), []);
    }

    private function validateEmployee(Request $request)
    {   // for only-registered employee only that has status 1
        return Validator::make($request->all(), [
            'email' => 'nullable|string|max:255',
            'campus_id' => 'nullable|numeric',
            'full_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'religion' => 'required|string|max:255',
            'experience' => 'nullable|string|max:255',
            'cnic_no' => 'required|string',
            'qualification' => 'nullable|string|max:255',
            'gender' => 'required|string|max:255',
            'marital_status' => 'nullable|string|max:255',
            'dob' => 'nullable|date|date_format:Y-m-d',
            'remarks' => 'nullable|string|max:255',
            'mobile_no' => 'nullable',
            'phone' => 'nullable|string',
            'address' => 'nullable|string|max:255',
        ]);
    }
    /**
     * appointed appointed appointed
     * appointed appointed appointed
     * appointed appointed appointed
     * appointed appointed appointed
     * appointed appointed appointed
     * appointed appointed appointed
     * appointed appointed appointed
     * appointed appointed appointed
     * appointed appointed appointed
     * appointed appointed appointed
     * appointed appointed appointed
     * appointed appointed appointed
     */

    /**
     * campus_id is given to get record of ask campus only.
     * $job_status_id is provided to get the record of on job employees, or retired or transfer or in service
     *
     * @return \Illuminate\Http\Response will teturn employee who are appointed
     */
    public function empIndex(Campus $campus, $job_status_id = 0) // appointed employee whose jobs status is given
    {
        $validator = Validator::make(['job_status_id' => $job_status_id], [
            'job_status_id' => ['required', Rule::in([0, 1, 2, 3, 4])],
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->employeeRepository->empIndex($campus, $job_status_id), []);
    }

    //get employees by campus name
    // public function CampusEmployees(EmployeesFilterByNameRequest $request)
    // {
    //     $employees = Employee::where('full_name','like',"%{$request->name_code}%")
    //                 ->orWhere('emp_code','like',"%{$request->name_code}%")
    //                 ->where('status' , 2)
    //                 ->where('job_status_id' , 1)
    //                 ->get();
    //     $employees->load('designation', 'jobStatus', 'bankAccount.bank_account_category','students.campus','students.studentClass','payScale');

    //     $employees = EmployeeResource::collection($employees);
    //     return $this->sendResponse($employees,[]);
    // }
    public function CampusEmployees(EmployeesFilterByNameRequest $request)
    {
        $campus_id = _campusId();

        if ($campus_id) {
            $employees = Employee::where('full_name', 'like', "%{$request->name_code}%")
                ->orWhere('emp_code', 'like', "%{$request->name_code}%")
                ->where('status', 2)
                ->where('job_status_id', 1)
                ->where('campus_id', $campus_id)
                ->get();
        } else {
            $employees = Employee::where('full_name', 'like', "%{$request->name_code}%")
                ->orWhere('emp_code', 'like', "%{$request->name_code}%")
                ->where('status', 2)
                ->where('job_status_id', 1)
                ->get();
        }

        $employees->load('designation', 'GPFund', 'jobStatus', 'bankAccount.bank_account_category', 'students.campus', 'students.studentClass', 'payScale');

        $employees = EmployeeResource::collection($employees);
        return $this->sendResponse($employees, []);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function empShow(Employee $employee)
    {
        if ($employee->status > 4) {
            return $this->sendError('this employee is only registered', [], 422);
        }

        return $this->sendResponse($this->employeeRepository->empShow($employee), []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function empStore(Request $request, Employee $employee)
    {
        $validator = $this->validateAppointedEmp($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->employeeRepository->empStore($request, $employee), []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function empUpdate(Request $request, Employee $employee)
    {
        $validator = $this->validateAppointedEmp($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        //in update we can update whole data so i aplied registraton and apointment valition both
        $validator = $this->validateEmployee($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->employeeRepository->empUpdate($request, $employee), []);
    }

    private function validateAppointedEmp(Request $request)
    {
        return Validator::make($request->all(), [
            'social_security_number' => 'nullable|string|max:10|min:5',
            'field_of_interest' => 'nullable|string|max:255',
            'campus_id' => 'nullable|numeric',
            'distinctions' => 'nullable|string|max:255',
            'objectives' => 'nullable|string|max:255',
            'duties_assigned' => 'nullable|string|max:255',
            // 'payment_type'   => ['required', Rule::in(['Bank', 'Cash'])],
            'bank_account_id' => 'nullable|string|max:255',
            'account_no' => 'nullable|string|max:255', //employee account
            'salery_days' => 'nullable|max:255',
            'joining_date' => 'required|string|max:255',
            'eobi_no' => 'nullable|string|max:255',

        ]);
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // SALARY Defining
    public function defineSalary(Request $request, Employee $employee)
    {
        if ($employee->status != 2) {
            return $this->sendError(['the employee must be appointed'], [], 422);
        }
        $validator = Validator::make($request->all(), [
            // 'designation_id'           => ['required', 'integer', 'min:1', 'max:30'],
            // 'qualification'       => 'required|string',
            'pay_scale_id' => 'required|integer',
            // 'account_no'     => 'required|string',
            // 'global_bank_id' => 'required|integer', 'max:20',
            // 'salery_days' => 'required|integer',
            // 'payment_type' => ['required', Rule::in(['Bank', 'Cash'])],
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
            'income_tax' => 'nullable|integer',
            'insurance' => 'nullable|integer',
            'van_charge' => 'nullable|integer',
            'other' => 'nullable|integer',
            'collected_gp_fund' => 'nullable|integer',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $response = $this->employeeRepository->defineSalary($request, $employee);
        if ($response) {
            return $this->sendResponse($response, []);
        }

        return $this->sendError(['internal server error'], [], 422);
    }

    public function empSalaryDetailShow(Employee $employee)
    {
        if ($employee->status == 1 || $employee->job_status_id == 5 || $employee->pay_scale_id == null) {
            return $this->sendError(['please select an appointed employee whose salary is defined'], [], 422);
        }

        return $this->sendResponse(new EmpSalaryDetailResource($employee), []);
    }

    public function showByCode($emp_code)
    {
        $validator = Validator::make(['emp_code' => $emp_code], [
            'emp_code' => 'nullable|string|exists:employees,emp_code',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        $employee = Employee::where('emp_code', $emp_code)->first();

        return $this->sendResponse(new EmployeeResource($employee), []);
    }
}
