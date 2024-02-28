<?php

namespace App\Repository;

use App\Http\Resources\EmployeeResource;
use App\Jobs\SendMessageJob;
use App\Models\Campus;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\GPFund;
use App\Repository\Interfaces\EmployeeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeRepository extends BaseRepository implements EmployeeRepositoryInterface
{
    /**$userRepository
     * ProfileRepository constructor.
     *
     * @param User $model
     */
    public function __construct(Employee $model)
    {
        parent::__construct($model);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function index(Campus $campus)
    {
        // return new StaffMemberResource($staffMember);
        return EmployeeResource::collection($campus->employees()->with('campus')->where('status', 1)->get()); // status 1 only rgister
    }

    public function allEmpForStatus(Request $request)
    {
        $employees = Employee::where('job_status_id', $request->job_status_id)->get();
        // return new StaffMemberResource($staffMember);
        return EmployeeResource::collection($employees); // status 1 only rgister
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) // only registration part
    {
        DB::beginTransaction();
        try {
            $emp = Employee::latest()->first();

            $employee = Employee::create([
                'campus_id' => $request->campus_id,
                'designation_id' => $request->designation_id,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'father_name' => $request->father_name,
                'nationality' => $request->nationality,
                'religion' => $request->religion,
                'experience' => $request->experience,
                'cnic_no' => $request->cnic_no,
                'qualification' => $request->qualification,
                'gender' => $request->gender,
                'marital_status' => $request->marital_status,
                'dob' => $request->dob,
                'remarks' => $request->remarks,
                'mobile_no' => $request->mobile_no,
                'phone' => $request->phone,
                'address' => $request->address,
                'auto_clear_deduction' => 0,
            ]);

            if ($emp) {
                $reg_code = $emp->reg_code;
            } else {
                $reg_code = 0;
            }
            $reg_code++;

            $employee->update(['reg_code' => $reg_code]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }
        DB::commit();

        return new EmployeeResource($employee);
    }

    public function show(Employee $employee)
    {
        return new EmployeeResource($employee);
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function update(Request $request, Employee $employee) // updating only the registered employee
    {
        DB::beginTransaction();
        try {
            $employee->update([
                'full_name' => $request->full_name,
                'father_name' => $request->father_name,
                'nationality' => $request->nationality,
                'campus_id' => $request->campus_id,
                'religion' => $request->religion,
                'experience' => $request->experience,
                'cnic_no' => $request->cnic_no,
                'qualification' => $request->qualification,
                'gender' => $request->gender,
                'marital_status' => $request->marital_status,
                'dob' => $request->dob,
                'remarks' => $request->remarks,
                'mobile_no' => $request->mobile_no,
                'phone' => $request->phone,
                'address' => $request->address,
                'email' => $request->email,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }
        DB::commit();

        return new EmployeeResource($employee);
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return response()->json('Employee successfully deleted');
    }

    /**
     * appointed employee
     * appointed employee
     * appointed employee
     * appointed employee
     * appointed employee
     * appointed employee
     * appointed employee
     * appointed employee
     * appointed employee
     * appointed employee
     * appointed employee
     * appointed employee
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function empIndex(Campus $campus, $job_status_id)
    {
        if ($job_status_id == 0) {
            $employees = Employee::where(['status' => 2])->get();
            $employees->load('designation', 'jobStatus', 'bankAccount.bank_account_category');

            return EmployeeResource::collection($employees);
        }
        $employees = Employee::where(['campus_id' => $campus->id, 'status' => 2, 'job_status_id' => $job_status_id])->get();
        $employees->load('designation', 'campus', 'jobStatus', 'bankAccount.bank_account_category', 'students.campus', 'students.studentClass', 'payScale');

        foreach ($employees as $employee) {
            $collected_gp_fund = $employee->gpFund;

            if ($collected_gp_fund) {
                $employee->collected_gp_fund = $collected_gp_fund->collected_amount;
            } else {
                $employee->collected_gp_fund = 0;
            }
        }

        return EmployeeResource::collection($employees);
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function empShow(Employee $employee)
    {
        $employee->load('designation', 'jobStatus', 'payScale', 'bankAccount.bank_account_category', 'salaryAllowance', 'salaryDeduction', 'employeeSalaries', 'GPFund');

        return new EmployeeResource($employee);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function empStore(Request $request, Employee $employee) //appointing after registration
    {

        DB::beginTransaction();
        try {
            $emp = Employee::where('status', '>', '1')
                ->where('emp_code', '!=', null)
                ->latest()
                ->first();
            $highest_emp_code = Employee::where('status', '>', '1')
                ->where('emp_code', '!=', null)
                ->orderBy('emp_code', 'desc')
                ->first();

            $old_emp_code = $employee->emp_code;

            $employee->update([
                'social_security_number' => $request->social_security_number,
                'field_of_interest' => $request->field_of_interest,
                'distinctions' => $request->distinctions,
                'objectives' => $request->objectives,
                'duties_assigned' => $request->duties_assigned,

                //   'payment_type'           => $request->payment_type,
                'bank_account_id' => $request->bank_account_id,
                'account_no' => $request->account_no,
                'salery_days' => $request->salery_days,
                'joining_date' => $request->joining_date,
                'eobi_no' => $request->eobi_no,
                'status' => 2,
                'job_status_id' => 1,
            ]);

            if ($old_emp_code == null) {
                $emp_code = $highest_emp_code->emp_code + 1;
                $employee->update(['emp_code' => $emp_code]);
            }

            $designation = Designation::find($employee->designation_id);

            $message = 'Congratulations!, ' . $employee->full_name . ' You have been appointed as ' . $designation->name . ' in  ' . _getUnitName() . ' from ' . $request->joining_date . ' Campus Name: '
                . Campus::find($employee->campus_id)->name . '.';

            $employee->GPFund()->create(['collected_amount' => 0]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }
        DB::commit();
        _sendSMS(2, $emp->mobile_no, $message);

        // if (config('app.sms_api')) {
        //     // if (_campusAllowedMessages($emp->campus_id) > 0) {
        //     SendMessageJob::dispatch(1, $emp->mobile_no, $message);
        //     // _campusMessageDecrement($emp->campus_id);
        //     // }
        // }

        return new EmployeeResource($employee);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function empUpdate(Request $request, Employee $employee)
    {
        DB::beginTransaction();
        try {
            $employee->update([
                'full_name' => $request->full_name,
                'father_name' => $request->father_name,
                'nationality' => $request->nationality,
                'religion' => $request->religion,
                'experience' => $request->experience,
                'cnic_no' => $request->cnic_no,
                'qualification' => $request->qualification,
                'gender' => $request->gender,
                'marital_status' => $request->marital_status,
                'dob' => $request->dob,
                'remarks' => $request->remarks,
                'mobile_no' => $request->mobile_no,
                'phone' => $request->phone,
                'address' => $request->address,
                'email' => $request->email,
                'campus_id' => $request->campus_id,

                'social_security_number' => $request->social_security_number,
                'field_of_interest' => $request->field_of_interest,
                'distinctions' => $request->distinctions,
                'objectives' => $request->objectives,
                'duties_assigned' => $request->duties_assigned,

                // 'payment_type'           => $request->payment_type,
                'bank_account_id' => $request->bank_account_id,
                'account_no' => $request->account_no,
                'salery_days' => $request->salery_days,
                'joining_date' => $request->joining_date,
                'eobi_no' => $request->eobi_no,
                'job_status_id' => $request->job_status_id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return $e->getMessage();
        }
        DB::commit();
        $employee->load('designation', 'jobStatus', 'payScale', 'bankAccount.bank_account_category', 'salaryAllowance', 'salaryDeduction');

        return new EmployeeResource($employee);
    }

    //salary define secti //////////////////////////////
    ///////////////////////////////////////////////////
    public function defineSalary(Request $request, Employee $employee)
    {
        DB::beginTransaction();
        try {
            $employee->update(['pay_scale_id' => $request->pay_scale_id]);
            $employee->update(['auto_clear_deduction' => $request->auto_clear_deduction]);

            $employee->salaryAllowance()->updateOrCreate([
                'employee_id' => $employee->id,
            ], [
                'hifz' => $request->hifz ? $request->hifz : 0,
                'hostel' => $request->hostel ? $request->hostel : 0,
                'college' => $request->college ? $request->college : 0,
                'increment' => $request->increment ? $request->increment : 0,
                'second_shift' => $request->second_shift ? $request->second_shift : 0,
                'additional_allowance' => $request->additional_allowance ? $request->additional_allowance : 0,
                'ugs' => $request->ugs ? $request->ugs : 0,
                'other' => $request->other_allowance ? $request->other_allowance : 0,
                'hod' => $request->hod ? $request->hod : 0,
                'science' => $request->science ? $request->science : 0,
                'extra_period' => $request->extra_period ? $request->extra_period : 0,
                'extra_coaching' => $request->extra_coaching ? $request->extra_coaching : 0,
                'convance' => $request->convance ? $request->convance : 0,

            ]);
            $employee->salaryDeduction()->updateOrCreate([
                'employee_id' => $employee->id,
            ], [
                'eobi' => $request->eobi ? $request->eobi : 0,
                'income_tax' => $request->income_tax ? $request->income_tax : 0,
                'insurance' => $request->insurance,
                'van_charge' => $request->van_charge ? $request->van_charge : 0,
                'other' => $request->other ? $request->other : 0,

            ]);

            if ($request->collected_gp_fund) {
                GPFund::updateOrCreate([
                    'employee_id' => $employee->id
                ], [
                    'collected_amount' => $request->collected_gp_fund,
                ]);
            }
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }
        DB::commit();
        $employee->load('designation', 'jobStatus', 'payScale', 'bankAccount.bank_account_category', 'salaryAllowance', 'salaryDeduction');

        return new EmployeeResource($employee);
    }

    public function empSalaryDetailShow(Employee $employee)
    {
    }
}
