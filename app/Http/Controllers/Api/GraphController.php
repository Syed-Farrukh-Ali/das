<?php

namespace App\Http\Controllers\Api;
// use App\Http\Controllers\Api\GraphController;

use App\Models\BankAccount;
use App\Models\Campus;
use App\Models\Concession;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\FeeChallan;
use App\Models\FeeChallanDetail;
use App\Models\GeneralLedger;
use App\Models\Session;
use App\Models\Student;
use App\Models\StudentLiableFee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GraphController extends BaseController
{
    public function totalStudentGraph()
    {
        // $month = explode("-", date('y-m-d'));
        $year = date('Y');
        $user = Auth::user();
        $roles = $user->roles()->pluck('name');
        $roles = (string) $roles[0];

        $student_ids = Student::whereYear('Joining_date', $year)->pluck('id')->unique();

        if ($roles === "Campus") {
            $total_students = Student::where('campus_id', $user->campus_id)->where('status', '=', 2)->count('id');
            $admit_students = Student::whereIn('id', $student_ids)->where('campus_id', $user->campus_id)->where('status', '=', 2)->count('id');
            $stuckOff_students = Student::whereYear('struck_off_date', $year)->where('campus_id', $user->campus_id)->where('status', '=', 4)->count('id');
            $leaving_students = Student::whereIn('id', $student_ids)->where('campus_id', $user->campus_id)->where('status', '=', 5)->count('id');
        } else {
            $total_students = Student::where('status', '=', 2)->count('id');
            $admit_students = Student::whereIn('id', $student_ids)->where('status', '=', 2)->count('id');
            $stuckOff_students = Student::whereYear('struck_off_date', $year)->where('status', '=', 4)->count('id');
            $leaving_students = Student::whereIn('id', $student_ids)->where('status', '=', 5)->count('id');
        }



        $current_students = $total_students - $admit_students;

        $data = [
            'total_students' =>  $total_students,
            'current_students' =>  $current_students,
            'admit_students' =>  $admit_students,
            'stuckOff_students' =>  $stuckOff_students,
            'leaving_students' =>  $leaving_students,
        ];
        return $this->sendResponse($data, [], 200);
    }

    public function totalConcessionStudent()
    {
        $data = [
            'total_concession' => [],
        ];

        $concession_ids = Concession::pluck('id')->toArray();

        foreach ($concession_ids as $concession_id) {
            $concession_data = [
                'concession_name' => Concession::find($concession_id)->title, // Use find() to get a single model instance
                'concession_type' => 0, // Initialize to 0
            ];

            $totalConcessionStudent = Student::where('concession_id', $concession_id)->where('status', 2)->count();

            $concession_data['concession_type'] = $totalConcessionStudent;

            $data['total_concession'][] = $concession_data;
        }

        return $this->sendResponse($data, [], 200);
    }

    public function totalPaidUnpaidFeeGraph()
    {
        $user = Auth::user();
        $roles = $user->roles()->pluck('name');
        $roles = (string) $roles[0];

        $month = explode("-", date('y-m-d'));

        $active_students = null;

        $checkingDate = now()->format('Y-m');

        if ($roles === "Campus") {
            $active_students = Student::where('status', '2')->where('campus_id', $user->campus_id)->pluck('id')->unique();
        } else {
            $active_students = Student::where('status', '2')->pluck('id')->unique();
        }
        $unpaid_amount = FeeChallan::where('status', '0')->whereIn('student_id', $active_students)->sum('payable');
        $unpaid_amount = (int) $unpaid_amount;

        $paid_amount = FeeChallan::where('status', '2')->where('received_date', 'LIKE', '%' . $checkingDate . '%')->whereIn('student_id', $active_students)->sum('payable');
        $paid_amount = (int) $paid_amount;

        $total_amount = $unpaid_amount + $paid_amount;

        $data = [
            // 'challan_ids' =>  $challan_ids,
            'paid' =>  $paid_amount,
            'unpaid' =>  $unpaid_amount,
            'total' =>  $total_amount,

        ];
        return $this->sendResponse($data, [], 200);
    }

    public function studentLiableFeeGraph()
    {
        $student_ids = Student::where('status', 2)->pluck('id')->unique();

        $total_fee = StudentLiableFee::whereIn('student_id', $student_ids)->where('fees_type_id', 4)->sum('amount');
        $total_fee = (int) $total_fee;

        $total_students = StudentLiableFee::whereIn('student_id', $student_ids)->where('fees_type_id', 4)->count('id');
        $average_fee = round($total_fee / $total_students);

        $total_concession = StudentLiableFee::whereIn('student_id', $student_ids)->where('fees_type_id', 4)->sum('concession_amount');
        $total_concession = (int) $total_concession;

        $no_fee_students = StudentLiableFee::whereIn('student_id', $student_ids)->where('fees_type_id', '=', 4)->where('amount', '=', 0)->count('id');

        $less_than_2000_students = StudentLiableFee::whereIn('student_id', $student_ids)->where('fees_type_id', '=', 4)->where('amount', '>=', 1)->where('amount', '<=', 2000)->count('id');

        $less_than_4500_students = StudentLiableFee::whereIn('student_id', $student_ids)->where('fees_type_id', '=', 4)->where('amount', '>=', 2001)->where('amount', '<=', 4500)->count('id');

        $above_than_4500_students = StudentLiableFee::whereIn('student_id', $student_ids)->where('fees_type_id', '=', 4)->where('amount', '>=', 4501)->count('id');

        $data = [

            'totalFee' =>  $total_fee,
            'averageFee' => $average_fee,
            'totalConcession' => $total_concession,
            'totalStudent' => $total_students,
            'noFeeStudent' => $no_fee_students,
            'lessThan2000Students' => $less_than_2000_students,
            'lessThan4500Students' => $less_than_4500_students,
            'aboveThan4500Students' => $above_than_4500_students,

        ];
        return $this->sendResponse($data, [], 200);
    }

    public function totalEmployeesSalaries()
    {
        $currentDate = Carbon::now();
        $month = $currentDate->subMonth();
        $month = $month->format('Y-m');

        $data = [
            'total_employees_salaries' => EmployeeSalary::where('salary_month', 'LIKE', "%" . $month . "%")->sum('gross_salary'),
            'campus_salaries_data' => [],
            'month' => $month,
        ];

        $campus_ids = Campus::pluck('id')->toArray();

        foreach ($campus_ids as $campus_id) {
            $campus_salary_data = [
                'campus_name' => Campus::find($campus_id)->name, // Use find() to get a single model instance
                'campus_salary' => 0, // Initialize to 0
            ];

            $totalCampusSalaries = EmployeeSalary::where('salary_month', 'LIKE', "%" . $month . "%")->where('campus_id', $campus_id)->sum('gross_salary');

            $campus_salary_data['campus_salary'] = $totalCampusSalaries;

            $data['campus_salaries_data'][] = $campus_salary_data;
        }

        return $this->sendResponse($data, [], 200);
    }

    public function totalEmployees()
    {
        $data = [
            'total_employees' => Employee::where('job_status_id', 1)->count('id'),
            'campus_employee_data' => [],
        ];

        $campus_ids = Campus::pluck('id')->toArray();

        foreach ($campus_ids as $campus_id) {
            $campus_employee_data = [
                'campus_name' => Campus::find($campus_id)->name, // Use find() to get a single model instance
                'campus_employee' => 0, // Initialize to 0
            ];

            $totalCampusEmployees = Employee::where('campus_id', $campus_id)->where('job_status_id', 1)->count();

            $campus_employee_data['campus_employee'] = $totalCampusEmployees;

            $data['campus_employee_data'][] = $campus_employee_data;
        }
        return $this->sendResponse($data, [], 200);
    }

    public function employeeTypes()
    {
        $user = Auth::user();
        $roles = $user->roles()->pluck('name');
        $roles = (string) $roles[0];

        $totalCampusEmployees = Employee::where('campus_id', $user->campus_id)->where('status', 2)->count('id');

        //Teachers
        $teacher =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 11)->where('status', 2)->count('id');
        $seniorTeacher =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 10)->where('status', 2)->count('id');
        $seniorScienceTeacher =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 15)->where('status', 2)->count('id');
        $qari =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 14)->where('status', 2)->count('id');
        $qaria =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 18)->where('status', 2)->count('id');
        $principal =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 32)->where('status', 2)->count('id');
        $vicePrincipal =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 24)->where('status', 2)->count('id');
        $inchargeCampus =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 33)->where('status', 2)->count('id');
        $teachingAssitant =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 22)->where('status', 2)->count('id');
        $lecturerAssitant =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 25)->where('status', 2)->count('id');
        $controllerExam =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 13)->where('status', 2)->count('id');
        $coordinator =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 21)->where('status', 2)->count('id');

        $campusTeachers = $teacher + $seniorTeacher + $seniorScienceTeacher + $qari +  $qaria + $principal + $principal +
            $vicePrincipal + $inchargeCampus + $teachingAssitant + $lecturerAssitant + $controllerExam + $coordinator;

        //ClassIV
        $technicalStaff =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 1)->where('status', 2)->count('id');
        $classIV =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 7)->where('status', 2)->count('id');
        $sweeper =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 8)->where('status', 2)->count('id');
        $storeKeeper =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 12)->where('status', 2)->count('id');
        $driver =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 26)->where('status', 2)->count('id');
        $cook =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 27)->where('status', 2)->count('id');
        $headCook =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 28)->where('status', 2)->count('id');
        $PA =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 34)->where('status', 2)->count('id');

        $campusClass4 = $technicalStaff + $classIV + $storeKeeper + $sweeper + $driver + $cook + $headCook + $PA;

        //Administrative Staff
        $asstAccount =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 2)->where('status', 2)->count('id');
        $computerOper =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 3)->where('status', 2)->count('id');
        $librarin =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 4)->where('status', 2)->count('id');
        $adminClerk =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 5)->where('status', 2)->count('id');
        $labAsst =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 6)->where('status', 2)->count('id');
        $pet =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 9)->where('status', 2)->count('id');
        $hostelAdmin =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 16)->where('status', 2)->count('id');
        $hostelSuperAdmin =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 17)->where('status', 2)->count('id');
        $Administator =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 19)->where('status', 2)->count('id');
        $civilSubEnginer =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 29)->where('status', 2)->count('id');
        $headIT =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 30)->where('status', 2)->count('id');
        $accountant =  Employee::where('campus_id', $user->campus_id)->where('designation_id', 31)->where('status', 2)->count('id');

        $campusAdministrative = $asstAccount + $computerOper +  $librarin +  $adminClerk +  $labAsst + $pet + $hostelAdmin +
            $hostelSuperAdmin + $Administator + $civilSubEnginer + $headIT + $accountant;


        $data = [
            'totalCampusEmployees' =>  $totalCampusEmployees,
            'campusTeachers' =>  $campusTeachers,
            'campusClass4' =>  $campusClass4,
            'campusAdministrative' =>  $campusAdministrative,
        ];
        return $this->sendResponse($data, [], 200);
    }

    public function totalAccountBalance()
    {
        $data = [
            'total_account_balance' => [],
        ];

        $sub_accounts = BankAccount::groupby('sub_account_id')->pluck('sub_account_id');

        $active_financial_year = Session::where('active_financial_year', '1')->value('id');

        foreach ($sub_accounts as $sub_account) {
            $total_account_balance_data = [
                'account_title' => '',
                'final_amount' => 0
            ];

            $final_credit = 0;
            $final_debit = 0;

            $credit = GeneralLedger::where('sub_account_id', $sub_account)
                ->where('session_id', $active_financial_year)
                ->sum('credit');

            $debit = GeneralLedger::where('sub_account_id', $sub_account)
                ->where('session_id', $active_financial_year)
                ->sum('debit');


            // return $this->sendResponse($credit, []);

            if ($credit - $debit > 0) {
                $final_credit = $credit - $debit;
            }

            if ($debit - $credit > 0) {
                $final_debit = $debit - $credit;
            }

            if ($final_credit == 0 && $final_debit == 0)
                continue;

            $account_title = BankAccount::where('sub_account_id', $sub_account)->pluck('account_title')->first();

            // return $this->sendResponse($final_debit, []);

            $final_amount = '';
            if ($final_credit)
                $final_amount = $final_credit;
            else
                $final_amount = $final_debit;

            $total_account_balance_data['account_title'] = $account_title;
            $total_account_balance_data['final_amount'] = $final_amount;

            $data['total_account_balance'][] = $total_account_balance_data;
            // $banksMessage .= $account_title . " = " . $final_amount . "\n";
        }

        // foreach ($concession_ids as $concession_id) {
        //     $concession_data = [
        //         'concession_name' => Concession::find($concession_id)->title, // Use find() to get a single model instance
        //         'concession_type' => 0, // Initialize to 0
        //     ];

        //     $totalConcessionStudent = Student::where('concession_id', $concession_id)->where('status', 2)->count();

        //     $concession_data['concession_type'] = $totalConcessionStudent;

        //     $data['total_concession'][] = $concession_data;
        // }

        return $this->sendResponse($data, [], 200);
    }
}


// Student Fee Graphs
            // Route::get('/total_paid_unpaid_fee', [GraphController::class, 'totalPaidUnpaidFeeGraph']);
            // Route::get('/total-student-graph', [GraphController::class, 'totalStudentGraph']);
            // Route::get('/student_liable_fee_graph', [GraphController::class, 'studentLiableFeeGraph']);
            // Route::get('/total_concession_student_graph', [GraphController::class, 'totalConcessionStudent']);
            // Route::get('/total_employees_salaries_graph', [GraphController::class, 'totalEmployeesSalaries']);
            // Route::get('/total_employees_graph', [GraphController::class, 'totalEmployees']);
            // Route::get('/employees_type_graph', [GraphController::class, 'employeeTypes']);
