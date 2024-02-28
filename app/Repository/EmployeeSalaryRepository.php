<?php

namespace App\Repository;

use App\Models\BankAccount;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\FeeChallan;
use App\Models\Session;
use App\Models\Setting;
use App\Models\Student;
use App\Repository\Interfaces\EmployeeSalaryRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeSalaryRepository extends BaseRepository implements EmployeeSalaryRepositoryInterface
{
    /**$userRepository
     * ProfileRepository constructor.
     *
     * @param User $model
     */
    public function __construct(EmployeeSalary $model)
    {
        parent::__construct($model);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return false|\Illuminate\Http\Response
     */
    public function singleEmpSalary(Request $request, Employee $employee)
    {
        try {
            DB::beginTransaction();
            $total_fee_amount = 0;

            if ($employee->students->isNotEmpty()) {
                $student_fee_month = Carbon::parse($request->salary_month)->addMonth();
                $childs = $employee->students()->where('status', '=', '2')->get();
                $std_ids = $childs->pluck('id');
                $feechallans = FeeChallan::with([
                    'feeChallanDetails' => function ($query) use ($student_fee_month) {
                        $query->whereDate('fee_month', '=', $student_fee_month);
                    }
                ])
                    ->where(['status' => 0, 'parent_id' => null])
                    ->whereIn('student_id', $std_ids)
                    ->get();

                $total_fee_details_amount = 0;

                foreach ($feechallans as $feechallan) {
                    $total_fee_details_amount += $feechallan->feeChallanDetails->sum('amount');
                }

                $total_fee_amount = $total_fee_details_amount;
            }

            // for calculating loan refund
            $loan_refund = 0;
            $loan = $employee->loans->first();
            if ($loan) {

                $installment = $loan->monthly_loan_installment;
                $credit = $loan->subAccount
                    ->general_ledgers
                    ->sum('credit');

                $debit = $loan->subAccount
                    ->general_ledgers
                    ->sum('debit');

                $remaining_amount = $debit - $credit;

                if ($remaining_amount > $installment || $remaining_amount == $installment) {
                    $loan_refund = $installment;
                } else {
                    $loan_refund = $remaining_amount;
                }

                //                 $loan_refund = $loan->monthly_loan_installment;
                // $loan->decrement('balance', $loan_refund);
                //
                //                 if ($loan->balance == 0) {
                //                     $loan->update(['status' => 0,'monthly_loan_installment' => 0]);
                //                 }
            }

            $payScale = $employee->payScale;
            $salaryAllowance = $employee->salaryAllowance;
            $salaryDeduction = $employee->salaryDeduction;

            $job_status_id = $request->job_status_id ?? 0;

            $gp_fund_Amt = 0;
            if ($employee->joining_date) {
                $years = Carbon::now()->diffInYears($employee->joining_date);
                $gpFundYears = Setting::where('id', 1)->value('gp_fund_years');

                if ($years < $gpFundYears && $job_status_id != 2) {
                    //$employee->GPFund->increment('collected_amount', $payScale->gp_fund);
                    $gp_fund_Amt = $payScale->gp_fund;
                }
            }

            $welfare_fund_ = 0;
            if ($job_status_id != 2) {
                $welfare_fund_ = $payScale->welfare_fund;
            }

            $gpf_return_value = 0;

            if ($request->is_returning_gpf) {
                $job_status_id = $request->job_status_id ?? 2;
                $employee->update(['job_status_id' => $job_status_id]);

                if (isset($request->gpf_return_amount)) {
                    $gpf_return_value = $request->gpf_return_amount;
                    // $employee->GPFund->decrement('collected_amount', $gpf_return_value);
                } else {
                    $gpf_return_value = $employee->GPFund->collected_amount;
                    // $employee->GPFund()->update(['collected_amount' => 0]);
                }
            }

            if ($request->days <= 0) {
                $hifz = 0;
                $hostel = 0;
                $college = 0;
                $additional_allowance = 0;
                $increment = 0;
                $second_shift = 0;
                $ugs = 0;
                $other = 0;
                $hod = 0;
                $science = 0;
                $extra_period = 0;
                $extra_coaching = 0;
                $convance = 0;
                $eobi = 0;
                $income_tax = 0;
                $insurance = 0;
                $van_charge = 0;
                $other_deduction = 0;
                $welfare_fund = 0;
                $gp_fund = 0;
                $loan_refund = 0;
                $child_fee_deduction = 0;
                $basic = 0;
            } else {
                $hifz = $salaryAllowance->hifz ?? 0;
                $hostel = $salaryAllowance->hostel ?? 0;
                $college = $salaryAllowance->college ?? 0;
                $additional_allowance = $salaryAllowance->additional_allowance ?? 0;
                $increment = $salaryAllowance->increment ?? 0;
                $second_shift = $salaryAllowance->second_shift ?? 0;
                $ugs = $salaryAllowance->ugs ?? 0;
                $other = $salaryAllowance->other ?? 0;
                $hod = $salaryAllowance->hod ?? 0;
                $science = $salaryAllowance->science ?? 0;
                $extra_period = $salaryAllowance->extra_period ?? 0;
                $extra_coaching = $salaryAllowance->extra_coaching ?? 0;
                $convance = $salaryAllowance->convance ?? 0;
                $eobi = $salaryDeduction->eobi ?? 0;
                $income_tax = $salaryDeduction->income_tax ?? 0;
                $insurance = $salaryDeduction->insurance ?? 0;
                $van_charge = $salaryDeduction->van_charge ?? 0;
                $other_deduction = $salaryDeduction->other ?? 0;
                $welfare_fund = $welfare_fund_;
                $gp_fund = $gp_fund_Amt;
                $child_fee_deduction = $total_fee_amount;
                $basic = $payScale->basic;
            }

            //get active financial year
            $sessionId = Session::where('active_financial_year', 1)->value('id');

            $empSalary = $employee->employeeSalaries()->updateOrCreate(
                [
                    'salary_month' => $request->salary_month,
                ],
                [
                    'campus_id' => $employee->campus_id,
                    // 'session_id' => $request->year_id,
                    'session_id' => $sessionId,
                    'bank_account_id' => $employee->bank_account_id,
                    'account_no' => $employee->account_no,
                    'net_pay' => 0,
                    'gross_salary' => 0,
                    // allowance
                    'hifz' => $hifz,
                    'hostel' => $hostel,
                    'college' => $college,
                    'additional_allowance' => $additional_allowance,
                    'increment' => $increment,
                    'second_shift' => $second_shift,
                    'ugs' => $ugs,
                    'other_allowance' => $other,
                    'hod' => $hod,
                    'science' => $science,
                    'extra_period' => $extra_period,
                    'extra_coaching' => $extra_coaching,
                    'convance' => $convance,
                    //gpf return to employee on last salary
                    'gpf_return' => $gpf_return_value,

                    // deductions
                    'eobi' => $eobi,
                    'income_tax' => $income_tax,
                    'insurance' => $insurance,
                    'van_charge' => $van_charge,
                    'other_deduction' => $other_deduction,
                    'child_fee_deduction' => $child_fee_deduction,
                    // funds from pay salary
                    'gp_fund' => $gp_fund,
                    'welfare_fund' => $welfare_fund,
                    'loan_refund' => $loan_refund,
                    'basic_pay' => $basic,
                ]
            );

            $total_allowance = $hifz +
                $hostel +
                $college +
                $additional_allowance +
                $increment +
                $second_shift +
                $ugs +
                $other +
                $hod +
                $science +
                $extra_period +
                $extra_coaching +
                $convance;

            $total_deduction = $eobi +
                $income_tax +
                $insurance +
                $van_charge +
                $other_deduction +

                ////deduction other than salary_deduction
                $welfare_fund +
                $gp_fund +
                $loan_refund +
                $child_fee_deduction;

            $real_gross_salary = $payScale->basic + $total_allowance;

            $subtracting_amount = 0;
            $extra_amount = 0;

            if ($request->days <= 0) {
                $single_day_amount = $real_gross_salary / 30;
                $subtracting_amount = round($single_day_amount * $request->days);
            } elseif ($request->days > 0 and $request->days < 30) {
                $single_day_amount = $real_gross_salary / 30;
                $miss_days = 30 - $request->days;
                $subtracting_amount = round($single_day_amount * $miss_days);
            } elseif ($request->days > 30) {
                $single_day_amount = $real_gross_salary / 30;
                $extra_days = $request->days - 30;
                $extra_amount = round($single_day_amount * $extra_days);
            }
            //  $basic_pay = $payScale->basic - $subtracting_amount + $extra_amount;
            ##############################################################################

            $BP =  $payScale->basic;
            $AA =  $additional_allowance;
            $H =   $hostel;
            $INC = $increment;
            $UGS = $ugs;
            $HOD = $hod;
            $SCI = $science;
            $EXP = $extra_period;
            $EXC = $extra_coaching;


            $TOTAL = $BP + $AA + $H + $INC + $UGS + $HOD + $SCI + $EXP + $EXC;
            $extra_pay = $subtracting_amount + $extra_amount;

            if ($TOTAL > 0 || $TOTAL < 0) {
                $BP_V =    round($extra_pay * $BP     / $TOTAL);
                $AA_V =    round($extra_pay * $AA     / $TOTAL);
                $H_V =     round($extra_pay * $H      / $TOTAL);
                $INC_V =   round($extra_pay * $INC    / $TOTAL);
                $UGS_V =   round($extra_pay * $UGS    / $TOTAL);
                $HOD_V =   round($extra_pay * $HOD    / $TOTAL);
                $SCI_V =   round($extra_pay * $SCI    / $TOTAL);
                $EXP_V =   round($extra_pay * $EXP    / $TOTAL);
                $EXC_V =   round($extra_pay * $EXC    / $TOTAL);

                $total_of_splitted_extra = $BP_V + $AA_V + $H_V + $INC_V + $UGS_V + $HOD_V + $SCI_V + $EXP_V + $EXC_V;
            } else {
                $BP_V = 0;
                $total_of_splitted_extra = 0;
            }

            $difference = $total_of_splitted_extra - $extra_pay;

            $BP_V = $BP_V - $difference;

            ///////////////////////
            if ($request->days > 0 || $request->days < 0 and $request->days < 30) {
                $BP =   $BP  - $BP_V;
                $AA =   $AA  - $AA_V;
                $H =    $H   - $H_V;
                $INC =  $INC - $INC_V;
                $UGS =  $UGS - $UGS_V;
                $HOD =  $HOD - $HOD_V;
                $SCI =  $SCI - $SCI_V;
                $EXP =  $EXP - $EXP_V;
                $EXC =  $EXC - $EXC_V;
            } elseif ($request->days > 30) {
                $BP =   $BP  + $BP_V;
                $AA =   $AA  + $AA_V;
                $H =    $H   + $H_V;
                $INC =  $INC + $INC_V;
                $UGS =  $UGS + $UGS_V;
                $HOD =  $HOD + $HOD_V;
                $SCI =  $SCI + $SCI_V;
                $EXP =  $EXP + $EXP_V;
                $EXC =  $EXC + $EXC_V;
            }
            // dd($subtracting_amount,$extra_amount,13904,$BP,$BP_V);

            ##############################################################################
            if ($request->days <= 0) {
                $BP = $subtracting_amount;
                $gross_salary = $subtracting_amount;
                $net_pay = $gross_salary + $gpf_return_value;
            } else {
                $gross_salary = $payScale->basic + $total_allowance - $subtracting_amount + $extra_amount;
                $net_pay = $gross_salary + $gpf_return_value - $total_deduction;
            }

            $empSalary->update([
                'net_pay' => $net_pay,
                'gross_salary' => $gross_salary,
                'days' => $request->days ?? 30,
                #############################
                'basic_pay' => $BP,
                'hostel' => $H,
                'additional_allowance' => $AA,
                'increment' => $INC,
                'ugs' => $UGS,
                'hod' => $HOD,
                'science' => $SCI,
                'extra_period' => $EXP,
                'extra_coaching' => $EXC,
                ###############################
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }
        if ($request->preview) {
            DB::rollBack();

            return $empSalary;
        }
        DB::commit();

        return $empSalary;
    }

    public function updateEmpSalary(Request $request, EmployeeSalary $employeeSalary)
    {
        DB::beginTransaction();
        try {

            $real_total_allowance = $employeeSalary->hifz +
                $employeeSalary->hostel +
                $employeeSalary->college +
                $employeeSalary->additional_allowance +
                $employeeSalary->increment +
                $employeeSalary->second_shift +
                $employeeSalary->ugs +
                $employeeSalary->other_allowance +
                $employeeSalary->hod +
                $employeeSalary->science +
                $employeeSalary->extra_period +
                $employeeSalary->extra_coaching +
                $employeeSalary->convance;

            $real_gross_salary = $employeeSalary->employee->payScale->basic + $real_total_allowance;

            $subtracting_amount = 0;
            $extra_amount = 0;
            if ($request->days > 0 and $request->days < 30) {
                $single_day_amount = $real_gross_salary / 30;
                $miss_days = 30 - $request->days;
                $subtracting_amount = round($single_day_amount * $miss_days);
            } elseif ($request->days > 30) {
                $single_day_amount = $real_gross_salary / 30;
                $extra_days = $request->days - 30;
                $extra_amount = round($single_day_amount * $extra_days);
            }

            $employeeSalary->update([
                'hifz' => $request->hifz,
                'hostel' => $request->hostel,
                'college' => $request->college,
                'additional_allowance' => $request->additional_allowance,
                'increment' => $request->increment,
                'second_shift' => $request->second_shift,
                'ugs' => $request->ugs,
                'other_allowance' => $request->other_allowance,
                'hod' => $request->hod,
                'science' => $request->science,
                'extra_period' => $request->extra_period,
                'extra_coaching' => $request->extra_coaching,
                'convance' => $request->convance,

                'eobi' => $request->eobi,
                'eobi_payments' => $request->eobi_payments,
                'income_tax' => $request->income_tax,
                'insurance' => $request->insurance,
                'van_charge' => $request->van_charge,
                'other_deduction' => $request->other_deduction,
                'gp_fund' => $request->gp_fund,
                'gpf_return' => $request->gpf_return,
                'welfare_fund' => $request->welfare_fund,
                'child_fee_deduction' => $request->child_fee_deduction,
                'loan_refund' => $request->loan_refund ?? $employeeSalary->loan_refund,
                'days' => $request->days == 0 ? 30 : $request->days,
                'basic_pay' => $employeeSalary->employee->payScale->basic + $extra_amount - $subtracting_amount,
                'bank_account_id' => $request->bank_account_id,


            ]);
            $total_allowance = $employeeSalary->hifz +
                $employeeSalary->hostel +
                $employeeSalary->college +
                $employeeSalary->additional_allowance +
                $employeeSalary->increment +
                $employeeSalary->second_shift +
                $employeeSalary->ugs +
                $employeeSalary->other_allowance +
                $employeeSalary->hod +
                $employeeSalary->science +
                $employeeSalary->extra_period +
                $employeeSalary->extra_coaching +
                $employeeSalary->convance;

            $total_deduction = $employeeSalary->eobi +
                $employeeSalary->income_tax +
                $employeeSalary->insurance +
                $employeeSalary->van_charge +
                $employeeSalary->other_deduction +
                $employeeSalary->loan_refund +
                $employeeSalary->child_fee_deduction;

            $gross_salary = $employeeSalary->basic_pay + $total_allowance + $request->gpf_return;

            $net_pay = $gross_salary - $total_deduction - $employeeSalary->welfare_fund - $employeeSalary->gp_fund;

            $employeeSalary->update([
                'net_pay' => $net_pay,
                'gross_salary' => $gross_salary,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return $th->getMessage();
        }

        if ($request->preview == 1) {
            DB::rollBack();

            return $employeeSalary;
        }

        DB::commit();

        return $employeeSalary->load('employee');
    }

    public function getEmployeeList(Request $request)
    {
        $campus_id = $request->campus_id;
        $designation_ids = $request->designation_ids;

        return   $emps = Employee::when($request->bank_account_id, fn ($query) => $query->where('bank_account_id', $request->bank_account_id))->where('campus_id', $campus_id)->when($request->designation_ids, fn ($query) => $query->whereIn('designation_id', $designation_ids))->where('job_status_id', 1)->where('pay_scale_id', '!=', null)->get();
        $emps->load('payScale', 'salaryAllowance', 'salaryDeduction');

        return $emps;
    }

    public function bulkSalaryGenerateByList(Request $request)
    {
        $employees = Employee::whereIn('id', $request->employee_id)->get();
        $empAlreadyHaveGenerated = [];
        $salaries = Collection::make(new EmployeeSalary);

        DB::beginTransaction();
        try {
            foreach ($employees as $key => $employee) {
                if (EmployeeSalary::where(['employee_id' => $employee->id, 'salary_month' => $request->salary_month])->exists()) {
                    array_push($empAlreadyHaveGenerated, $employee);

                    continue;
                }

                $total_fee_amount = 0;
                if ($employee->students->isNotEmpty()) {

                    $student_fee_month = Carbon::parse($request->salary_month)->addMonth();

                    $childs = $employee->students()->where('status', '=', '2')->get();
                    $std_ids = $childs->pluck('id');
                    $feechallans = FeeChallan::with([
                        'feeChallanDetails' => function ($query) use ($student_fee_month) {
                            $query->whereDate('fee_month', '=', $student_fee_month);
                        }
                    ])
                        ->where(['status' => 0, 'parent_id' => null])
                        ->whereIn('student_id', $std_ids)
                        ->get();

                    $total_fee_details_amount = 0;

                    foreach ($feechallans as $feechallan) {
                        $total_fee_details_amount += $feechallan->feeChallanDetails->sum('amount');
                    }

                    $total_fee_amount = $total_fee_details_amount;
                }

                $loan_refund = 0;
                $loan = $employee->loans->first();
                if ($loan) {
                    $installment = $loan->monthly_loan_installment;
                    $credit = $loan->subAccount
                        ->general_ledgers
                        ->sum('credit');

                    $debit = $loan->subAccount
                        ->general_ledgers
                        ->sum('debit');

                    $remaining_amount = $debit - $credit;

                    if ($remaining_amount > $installment || $remaining_amount == $installment) {
                        $loan_refund = $installment;
                    } else {
                        $loan_refund = $remaining_amount;
                    }

                    //                 $loan_refund = $loan->monthly_loan_installment;
                    // $loan->decrement('balance', $loan_refund);
                    //
                    //                 if ($loan->balance == 0) {
                    //                     $loan->update(['status' => 0,'monthly_loan_installment' => 0]);
                    //                 }
                }

                $payScale = $employee->payScale;
                $salaryAllowance = $employee->salaryAllowance;
                $salaryDeduction = $employee->salaryDeduction;

                $job_status_id = $request->job_status_id ?? 0;

                $gp_fund_Amt = 0;
                if ($employee->joining_date) {
                    $years = Carbon::now()->diffInYears($employee->joining_date);
                    $gpFundYears = Setting::where('id', 1)->value('gp_fund_years');

                    if ($years < $gpFundYears && $job_status_id != 2) {
                        // Abdullah Tariq
                        // $employee->GPFund->increment('collected_amount', $payScale->gp_fund);
                        $gp_fund_Amt = $payScale->gp_fund;
                    }
                }

                $welfare_fund_ = 0;
                if ($job_status_id != 2) {
                    $welfare_fund_ = $payScale->welfare_fund;
                }

                $salary_days = $employee->salery_days;

                if ($salary_days <= 0) {
                    $hifz = 0;
                    $hostel = 0;
                    $college = 0;
                    $additional_allowance = 0;
                    $increment = 0;
                    $second_shift = 0;
                    $ugs = 0;
                    $other = 0;
                    $hod = 0;
                    $science = 0;
                    $extra_period = 0;
                    $extra_coaching = 0;
                    $convance = 0;
                    $eobi = 0;
                    $income_tax = 0;
                    $insurance = 0;
                    $van_charge = 0;
                    $other_deduction = 0;
                    $welfare_fund = 0;
                    $gp_fund = 0;
                    $loan_refund = 0;
                    $child_fee_deduction = 0;
                    $basic = 0;
                } else {
                    $hifz = $salaryAllowance->hifz ?? 0;
                    $hostel = $salaryAllowance->hostel ?? 0;
                    $college = $salaryAllowance->college ?? 0;
                    $additional_allowance = $salaryAllowance->additional_allowance ?? 0;
                    $increment = $salaryAllowance->increment ?? 0;
                    $second_shift = $salaryAllowance->second_shift ?? 0;
                    $ugs = $salaryAllowance->ugs ?? 0;
                    $other = $salaryAllowance->other ?? 0;
                    $hod = $salaryAllowance->hod ?? 0;
                    $science = $salaryAllowance->science ?? 0;
                    $extra_period = $salaryAllowance->extra_period ?? 0;
                    $extra_coaching = $salaryAllowance->extra_coaching ?? 0;
                    $convance = $salaryAllowance->convance ?? 0;
                    $eobi = $salaryDeduction->eobi ?? 0;
                    $income_tax = $salaryDeduction->income_tax ?? 0;
                    $insurance = $salaryDeduction->insurance ?? 0;
                    $van_charge = $salaryDeduction->van_charge ?? 0;
                    $other_deduction = $salaryDeduction->other ?? 0;
                    $welfare_fund = $welfare_fund_;
                    $gp_fund = $gp_fund_Amt;
                    $child_fee_deduction = $total_fee_amount;
                    $basic = $payScale->basic;
                }

                $empSalary = $employee->employeeSalaries()->updateOrCreate(
                    [
                        'salary_month' => $request->salary_month,
                    ],
                    [
                        'campus_id' => $employee->campus_id,
                        'session_id' => $request->year_id,
                        'bank_account_id' => $employee->bank_account_id,
                        'account_no' => $employee->account_no,
                        'net_pay' => 0,
                        'gross_salary' => 0,
                        // allowance
                        'hifz' => $hifz,
                        'hostel' => $hostel,
                        'college' => $college,
                        'additional_allowance' => $additional_allowance,
                        'increment' => $increment,
                        'second_shift' => $second_shift,
                        'ugs' => $ugs,
                        'other_allowance' => $other,
                        'hod' => $hod,
                        'science' => $science,
                        'extra_period' => $extra_period,
                        'extra_coaching' => $extra_coaching,
                        'convance' => $convance,
                        //gpf return to employee on last salary
                        'gpf_return' => 0,

                        // deductions
                        'eobi' => $eobi,
                        'income_tax' => $income_tax,
                        'insurance' => $insurance,
                        'van_charge' => $van_charge,
                        'other_deduction' => $other_deduction,
                        'child_fee_deduction' => $child_fee_deduction,
                        // funds from pay salary
                        'gp_fund' => $gp_fund,
                        'welfare_fund' => $welfare_fund,
                        'loan_refund' => $loan_refund,
                        'basic_pay' => $basic,
                    ]
                );

                $total_allowance = $hifz +
                    $hostel +
                    $college +
                    $additional_allowance +
                    $increment +
                    $second_shift +
                    $ugs +
                    $other +
                    $hod +
                    $science +
                    $extra_period +
                    $extra_coaching +
                    $convance;

                $total_deduction = $eobi +
                    $income_tax +
                    $insurance +
                    $van_charge +
                    $other_deduction +

                    ////deduction other than salary_deduction
                    $welfare_fund +
                    $gp_fund +
                    $loan_refund +
                    $child_fee_deduction;

                $real_gross_salary = $payScale->basic + $total_allowance;

                $subtracting_amount = 0;
                $extra_amount = 0;

                if ($salary_days <= 0) {
                    $single_day_amount = $real_gross_salary / 30;
                    $subtracting_amount = round($single_day_amount * $salary_days);
                } elseif ($salary_days > 0 and $salary_days < 30) {
                    $single_day_amount = $real_gross_salary / 30;
                    $miss_days = 30 - $salary_days;
                    $subtracting_amount = round($single_day_amount * $miss_days);
                } elseif ($salary_days > 30) {
                    $single_day_amount = $real_gross_salary / 30;
                    $extra_days = $salary_days - 30;
                    $extra_amount = round($single_day_amount * $extra_days);
                }
                //  $basic_pay = $payScale->basic - $subtracting_amount + $extra_amount;
                ##############################################################################

                $BP =  $payScale->basic;
                $AA =  $additional_allowance;
                $H =   $hostel;
                $INC = $increment;
                $UGS = $ugs;
                $HOD = $hod;
                $SCI = $science;
                $EXP = $extra_period;
                $EXC = $extra_coaching;


                $TOTAL = $BP + $AA + $H + $INC + $UGS + $HOD + $SCI + $EXP + $EXC;
                $extra_pay = $subtracting_amount + $extra_amount;

                if ($TOTAL > 0 || $TOTAL < 0) {
                    $BP_V =    round($extra_pay * $BP     / $TOTAL);
                    $AA_V =    round($extra_pay * $AA     / $TOTAL);
                    $H_V =     round($extra_pay * $H      / $TOTAL);
                    $INC_V =   round($extra_pay * $INC    / $TOTAL);
                    $UGS_V =   round($extra_pay * $UGS    / $TOTAL);
                    $HOD_V =   round($extra_pay * $HOD    / $TOTAL);
                    $SCI_V =   round($extra_pay * $SCI    / $TOTAL);
                    $EXP_V =   round($extra_pay * $EXP    / $TOTAL);
                    $EXC_V =   round($extra_pay * $EXC    / $TOTAL);

                    $total_of_splitted_extra = $BP_V + $AA_V + $H_V + $INC_V + $UGS_V + $HOD_V + $SCI_V + $EXP_V + $EXC_V;
                } else {
                    $BP_V = 0;
                    $total_of_splitted_extra = 0;
                }

                $difference = $total_of_splitted_extra - $extra_pay;

                $BP_V = $BP_V - $difference;

                ///////////////////////
                if ($salary_days > 0 || $salary_days < 0 and $salary_days < 30) {
                    $BP =   $BP  - $BP_V;
                    $AA =   $AA  - $AA_V;
                    $H =    $H   - $H_V;
                    $INC =  $INC - $INC_V;
                    $UGS =  $UGS - $UGS_V;
                    $HOD =  $HOD - $HOD_V;
                    $SCI =  $SCI - $SCI_V;
                    $EXP =  $EXP - $EXP_V;
                    $EXC =  $EXC - $EXC_V;
                } elseif ($salary_days > 30) {
                    $BP =   $BP  + $BP_V;
                    $AA =   $AA  + $AA_V;
                    $H =    $H   + $H_V;
                    $INC =  $INC + $INC_V;
                    $UGS =  $UGS + $UGS_V;
                    $HOD =  $HOD + $HOD_V;
                    $SCI =  $SCI + $SCI_V;
                    $EXP =  $EXP + $EXP_V;
                    $EXC =  $EXC + $EXC_V;
                }
                // dd($subtracting_amount,$extra_amount,13904,$BP,$BP_V);

                ##############################################################################
                if ($salary_days <= 0) {
                    $BP = $subtracting_amount;
                    $gross_salary = $subtracting_amount;
                    $net_pay = $gross_salary;
                } else {
                    $gross_salary = $payScale->basic + $total_allowance - $subtracting_amount + $extra_amount;
                    $net_pay = $gross_salary - $total_deduction;
                }

                $empSalary->update([
                    'net_pay' => $net_pay,
                    'gross_salary' => $gross_salary,
                    'days' => $salary_days,
                    #############################
                    'basic_pay' => $BP,
                    'hostel' => $H,
                    'additional_allowance' => $AA,
                    'increment' => $INC,
                    'ugs' => $UGS,
                    'hod' => $HOD,
                    'science' => $SCI,
                    'extra_period' => $EXP,
                    'extra_coaching' => $EXC,
                    ###############################
                ]);

                // if ($salary_days > 30 || $salary_days < 30) {
                //     $employee->update([
                //         'salery_days' => 30,
                //     ]);
                // }

                //                 $payScale = $employee->payScale;
                //                 $salaryAllowance = $employee->salaryAllowance;
                //                 $salaryDeduction = $employee->salaryDeduction;
                //                 //////////////////////
                //                 $total_fee_amount = 0;
                //                 if ($employee->students->isNotEmpty()) {
                //                     $childs = $employee->students;
                //                     $std_ids = $childs->pluck('id');
                //                     $feechallans = FeeChallan::where(['status' => 0, 'parent_id' => null])->whereIn('student_id', $std_ids)->get();
                //                     $total_fee_amount = $feechallans->pluck('payable')->sum();
                //                 }
                //
                //                 $loan_refund = 0;
                //                 $loan = $employee->loans->first();
                //                 if ($loan) {
                //                     $installment = $loan->monthly_loan_installment;
                //                     $credit = $loan->subAccount
                //                         ->general_ledgers
                //                         ->sum('credit');
                //
                //                     $debit = $loan->subAccount
                //                         ->general_ledgers
                //                         ->sum('debit');
                //
                //                     $remaining_amount = $debit-$credit;
                //
                //                     if ($remaining_amount > $installment || $remaining_amount == $installment){
                //                         $loan_refund = $installment;
                //                     }else {
                //                         $loan_refund = $remaining_amount;
                //                     }
                //
                //                     //  $loan->decrement('balance', $loan_refund);
                ////
                ////                     if ($loan->balance == 0) {
                ////                         $loan->update(['status' => 0,'monthly_loan_installment' => 0]);
                ////                     }
                //                 }
                //
                //                 $gp_fund = 0;
                //                 if ($employee->joining_date)
                //                 {
                //                     $years = Carbon::now()->diffInYears($employee->joining_date);
                //
                //                     if ($years < 5)
                //                         $gp_fund = $payScale->gp_fund;
                //                 }
                //
                //                 ////////////////////
                //                 $empSalary = $employee->employeeSalaries()->create([
                //                     'campus_id' => $employee->campus_id,
                //                     'session_id' => $request->year_id,
                //                     'bank_account_id' => $employee->bank_account_id,
                //                     'salary_month' => $request->salary_month,
                //                     'account_no' => $employee->account_no,
                //                     'net_pay' => 0,
                //                     'gross_salary' => 0,
                //                     // allowance
                //                     'hifz' => $salaryAllowance->hifz,
                //                     'hostel' => $salaryAllowance->hostel,
                //                     'college' => $salaryAllowance->college,
                //                     'additional_allowance' => $salaryAllowance->additional_allowance,
                //                     'increment' => $salaryAllowance->increment,
                //                     'second_shift' => $salaryAllowance->second_shift,
                //                     'ugs' => $salaryAllowance->ugs,
                //                     'other_allowance' => $salaryAllowance->other,
                //                     'hod' => $salaryAllowance->hod,
                //                     'science' => $salaryAllowance->science,
                //                     'extra_period' => $salaryAllowance->extra_period,
                //                     'extra_coaching' => $salaryAllowance->extra_coaching,
                //                     'convance' => $salaryAllowance->convance,
                //                     // deductions
                //                     'eobi' => $salaryDeduction->eobi,
                //                     'income_tax' => $salaryDeduction->income_tax,
                //                     'insurance' => $salaryDeduction->insurance,
                //                     'van_charge' => $salaryDeduction->van_charge,
                //                     'other_deduction' => $salaryDeduction->other,
                //                     'child_fee_deduction' => $total_fee_amount,
                //                     // funds from pay salary
                //                     'gp_fund' => $gp_fund,
                //                     'welfare_fund' => $payScale->welfare_fund,
                //                     'loan_refund' => $loan_refund,
                //                     'basic_pay' => $payScale->basic,
                //                 ]);
                //
                //                 $total_allowance = $salaryAllowance->hifz +
                //                                    $salaryAllowance->hostel +
                //                                    $salaryAllowance->college +
                //                                    $salaryAllowance->additional_allowance +
                //                                    $salaryAllowance->increment +
                //                                    $salaryAllowance->second_shift +
                //                                    $salaryAllowance->ugs +
                //                                    $salaryAllowance->other +
                //                                    $salaryAllowance->hod +
                //                                    $salaryAllowance->science +
                //                                    $salaryAllowance->extra_period +
                //                                    $salaryAllowance->extra_coaching +
                //                                    $salaryAllowance->convance;
                //
                //                 $total_deduction = $salaryDeduction->eobi +
                //                                    $salaryDeduction->income_tax +
                //                                    $salaryDeduction->insurance +
                //                                    $salaryDeduction->van_charge +
                //                                    $salaryDeduction->other +
                //                                    $empSalary->loan_refund +
                //                                    $empSalary->child_fee_deduction;
                //
                //                 $gross_salary = $payScale->basic + $total_allowance;
                //
                //                 $net_pay = $payScale->basic - $payScale->welfare_fund - $gp_fund;
                //                 $net_pay = $net_pay + $total_allowance - $total_deduction;
                //
                //                 $empSalary->update(['net_pay' => $net_pay, 'gross_salary' => $gross_salary]);
                //
                //                 $collected_gpf = $employee->GPFund->collected_amount + $gp_fund;
                //                 $employee->GPFund()->update(['collected_amount' => $collected_gpf]);
                //                 //  array_push($salaries,$empSalary);
                $salaries->push($empSalary);
            }
        } catch (\Throwable $e) {
            DB::rollBack();

            return $e->getMessage();
        }

        $data = [
            'empAlreadyHaveGenerated' => $empAlreadyHaveGenerated,
            'salaries' => $salaries->load('employee'),
        ];

        if ($request->preview == 1) {
            DB::rollBack();
            return $data;
        }
        DB::commit();
        return $data;
    }


    //  $salary  = 12000 + 5000 + 4000 + 8000 ;

    //  $deduction = 6000 ;

    //  $total_salary = 29000 ;

    //  $salry_ratio = 43 + 17 + 13 + 27 ; // flatten
    //  $ded_ratio_amount = 2580 + 1020 + 780 + 1620 ; // flatten
}
