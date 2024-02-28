<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\FeeChallanDetailResource;
use App\Http\Resources\FeeChallanResource;
use App\Http\Resources\StudentResourceShortReport;
use App\Http\Resources\FeeChallanResourceCopy;
use App\Http\Resources\StudentResource;
use Illuminate\Http\Request;
use App\Http\Resources\StudentLiableFeeResource;
use App\Models\Session;
use DB;
use App\Models\Student;
use App\Models\FeeChallan;
use App\Models\FeeChallanDetail;
use App\Models\Campus;
use App\Models\BankAccount;
use App\Models\BankAccountCategory;
use App\Models\CampusClass;
use App\Models\StudentClass;
use App\Models\ClassSection;
use App\Models\StudentLiableFee;
use App\Models\GlobalSection;
use App\Models\AccountChart;
use App\Models\AccountGroup;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\Voucher;
use App\Models\Attendance;
use App\Models\ChequePaySalary;

class StudentsFeeReportsController extends BaseController
{
    public function monthlyAttendanceDetails(Request $req)
    {
        $sis = $req->sid;
        $start = $req->starting;
        $end = $req->ending;
        $stu = Student::where('admission_id', $sis)->pluck('id');
        $atc = Attendance::where('student_id', $stu)->whereBetween('date', [$start, $end])->orderBy('attendance_status_id')->get();
        $ctc = Student::where('admission_id', $sis)->get();
        $ctc->load('studentClass', 'globalSection', 'campus');
        return ['attendance' => $atc, 'stu' => $ctc];
    }

    public function monthlyAttendance(Request $req)
    {
        //return Attendance::select('select IFNULL(count(student_id),0) from attendances where attendance_status_id=1 group by student_id','select IFNULL(count(student_id),0) from attendances where attendance_status_id=2 group by student_id','select IFNULL(count(student_id),0) from attendances where attendance_status_id=3 group by student_id','select IFNULL(count(student_id),0) from attendances where attendance_status_id=4 group by student_id','select IFNULL(count(student_id),0) from attendances where attendance_status_id=5 group by student_id','select IFNULL(count(student_id),0) from attendances where attendance_status_id=6  group by student_id')->get();
        //return EmployeeSalary::all();
        //return Attendance::selectRaw('count(student_id) as number_of_orders, attendance_status_id,student_id')
        //->groupBy('student_id')->groupBy('attendance_status_id')->get();
        $campusid = $req->campus_id;
        $classid = $req->student_class_id;
        $secid = $req->global_section_id;
        $start = $req->starting;
        $end = $req->ending;
        $atc = Attendance::selectRaw('ifnull(count(student_id),0) as count,student_id,attendance_status_id')->whereIn('attendance_status_id', [1, 2, 3, 4, 5, 6, 7])->where('campus_id', $campusid)->where('student_class_id', $classid)->where('global_section_id', $secid)->whereBetween('date', [$start, $end])->groupBy('student_id')->groupBy('attendance_status_id')->get();
        $ctc = Attendance::with('student')->select('student_id')->where('campus_id', $campusid)->where('student_class_id', $classid)->where('global_section_id', $secid)->groupBy('student_id')->get();
        return ['attendance' => $atc, 'stu' => $ctc];
    }
    public function monthlyAttendance1(Request $req)
    {
        $sis = $req->sid;
        $start = $req->starting;
        $end = $req->ending;
        $stu = Student::where('admission_id', $sis)->pluck('id');
        $atc = Attendance::selectRaw('ifnull(count(student_id),0) as count,student_id,attendance_status_id')->whereIn('attendance_status_id', [1, 2, 3, 4, 5, 6, 7])->where('student_id', $stu)->whereBetween('date', [$start, $end])->groupBy('student_id')->groupBy('attendance_status_id')->get();
        $ctc = Attendance::with('student')->select('student_id')->where('student_id', $stu)->groupBy('student_id')->get();
        return ['attendance' => $atc, 'stu' => $ctc];
    }

    //     public function monthlysalary(Request $req){
    //         $dts=$req->dt;
    // return EmployeeSalary::select([DB::raw("SUM(net_pay) as netpay"),DB::raw("SUM(basic_pay) as basicpay"),DB::raw("SUM(gross_salary) as gross"),DB::raw("SUM(hifz) as hifz"),DB::raw("SUM(hostel) as hostel"),DB::raw("SUM(college) as college"),DB::raw("SUM(additional_allowance) as additional"),DB::raw("SUM(increment) as increment"),DB::raw("SUM(second_shift) as secondshift"),DB::raw("SUM(ugs) as ugs"),DB::raw("SUM(other_allowance) as otherallowance"),DB::raw("SUM(hod) as hod"),DB::raw("SUM(science) as science"),DB::raw("SUM(extra_period) as extraperiod"),DB::raw("SUM(extra_coaching) as coaching"),DB::raw("SUM(convance) as convance"),DB::raw("SUM(eobi_payments) as eobipayments"),DB::raw("SUM(gpf_return) as gpfreturn"),DB::raw("SUM(eobi) as eobi"),DB::raw("SUM(income_tax) as incometex"),DB::raw("SUM(insurance) as insurance"),DB::raw("SUM(van_charge) as vancharge"),DB::raw("SUM(other_deduction) as otherded"),DB::raw("SUM(child_fee_deduction) as childfee"),DB::raw("SUM(gp_fund) as gp"),DB::raw("SUM(welfare_fund) as welfarefund"),DB::raw("SUM(loan_refund) as loanrefound")])->where('salary_month',$dts)->where('status', '=', 0)->get();
    // //return EmployeeSalary::all();


    //     }
    //     public function monthlysalary1(Request $req){
    //         $dts=$req->dt;
    //         $cmp=$req->cmp;
    // return EmployeeSalary::select([DB::raw("SUM(net_pay) as netpay"),DB::raw("SUM(basic_pay) as basicpay"),DB::raw("SUM(gross_salary) as gross"),DB::raw("SUM(hifz) as hifz"),DB::raw("SUM(hostel) as hostel"),DB::raw("SUM(college) as college"),DB::raw("SUM(additional_allowance) as additional"),DB::raw("SUM(increment) as increment"),DB::raw("SUM(second_shift) as secondshift"),DB::raw("SUM(ugs) as ugs"),DB::raw("SUM(other_allowance) as otherallowance"),DB::raw("SUM(hod) as hod"),DB::raw("SUM(science) as science"),DB::raw("SUM(extra_period) as extraperiod"),DB::raw("SUM(extra_coaching) as coaching"),DB::raw("SUM(convance) as convance"),DB::raw("SUM(eobi_payments) as eobipayments"),DB::raw("SUM(gpf_return) as gpfreturn"),DB::raw("SUM(eobi) as eobi"),DB::raw("SUM(income_tax) as incometex"),DB::raw("SUM(insurance) as insurance"),DB::raw("SUM(van_charge) as vancharge"),DB::raw("SUM(other_deduction) as otherded"),DB::raw("SUM(child_fee_deduction) as childfee"),DB::raw("SUM(gp_fund) as gp"),DB::raw("SUM(welfare_fund) as welfarefund"),DB::raw("SUM(loan_refund) as loanrefound")])->where('campus_id',$cmp)->where('salary_month',$dts)->where('status', '=', 0)->get();
    // //return EmployeeSalary::all();


    //     }
    //      public function monthlysalary2(Request $req){
    //         $tits="0";
    //         $dts=$req->dt;
    //         $cmp=$req->xx;
    //          $vids=ChequePaySalary::where('cheque_number',$cmp)->get();
    //         foreach($vids as $a){
    //          $tits=$a['id'];
    //         }
    // return EmployeeSalary::select([DB::raw("SUM(net_pay) as netpay"),DB::raw("SUM(basic_pay) as basicpay"),DB::raw("SUM(gross_salary) as gross"),DB::raw("SUM(hifz) as hifz"),DB::raw("SUM(hostel) as hostel"),DB::raw("SUM(college) as college"),DB::raw("SUM(additional_allowance) as additional"),DB::raw("SUM(increment) as increment"),DB::raw("SUM(second_shift) as secondshift"),DB::raw("SUM(ugs) as ugs"),DB::raw("SUM(other_allowance) as otherallowance"),DB::raw("SUM(hod) as hod"),DB::raw("SUM(science) as science"),DB::raw("SUM(extra_period) as extraperiod"),DB::raw("SUM(extra_coaching) as coaching"),DB::raw("SUM(convance) as convance"),DB::raw("SUM(eobi_payments) as eobipayments"),DB::raw("SUM(gpf_return) as gpfreturn"),DB::raw("SUM(eobi) as eobi"),DB::raw("SUM(income_tax) as incometex"),DB::raw("SUM(insurance) as insurance"),DB::raw("SUM(van_charge) as vancharge"),DB::raw("SUM(other_deduction) as otherded"),DB::raw("SUM(child_fee_deduction) as childfee"),DB::raw("SUM(gp_fund) as gp"),DB::raw("SUM(welfare_fund) as welfarefund"),DB::raw("SUM(loan_refund) as loanrefound")])->where('cheque_pay_salary_id',$tits)->where('salary_month',$dts)->where('status', '=', 0)->get();
    // //return EmployeeSalary::all();


    //     }
    //      public function monthlysalary3(Request $req){
    //          $tits="0";
    //         $dts=$req->dt;
    //         $cmp=$req->xx;
    //         $vids=Voucher::where('voucher_no',$cmp)->get();
    //         foreach($vids as $a){
    //          $tits=$a['id'];
    //         }

    // return EmployeeSalary::select([DB::raw("SUM(net_pay) as netpay"),DB::raw("SUM(basic_pay) as basicpay"),DB::raw("SUM(gross_salary) as gross"),DB::raw("SUM(hifz) as hifz"),DB::raw("SUM(hostel) as hostel"),DB::raw("SUM(college) as college"),DB::raw("SUM(additional_allowance) as additional"),DB::raw("SUM(increment) as increment"),DB::raw("SUM(second_shift) as secondshift"),DB::raw("SUM(ugs) as ugs"),DB::raw("SUM(other_allowance) as otherallowance"),DB::raw("SUM(hod) as hod"),DB::raw("SUM(science) as science"),DB::raw("SUM(extra_period) as extraperiod"),DB::raw("SUM(extra_coaching) as coaching"),DB::raw("SUM(convance) as convance"),DB::raw("SUM(eobi_payments) as eobipayments"),DB::raw("SUM(gpf_return) as gpfreturn"),DB::raw("SUM(eobi) as eobi"),DB::raw("SUM(income_tax) as incometex"),DB::raw("SUM(insurance) as insurance"),DB::raw("SUM(van_charge) as vancharge"),DB::raw("SUM(other_deduction) as otherded"),DB::raw("SUM(child_fee_deduction) as childfee"),DB::raw("SUM(gp_fund) as gp"),DB::raw("SUM(welfare_fund) as welfarefund"),DB::raw("SUM(loan_refund) as loanrefound")])->where('voucher_id',$tits)->where('salary_month',$dts)->where('status', '=', 0)->get();
    // //return EmployeeSalary::all();


    //     }

    public function charts(Request $req)
    {
        return AccountGroup::select('title')->get();
    }

    public function fex(Request $req)
    {
        $idz = $req->id;
        $dates = $req->date;
        $type = $req->type;
        // return FeeChallanDetail::with('feeChallanreport')->with('student')->with('studentClassreport')->where('fee_month', '2023-05-01')->where('fees_type_id','4')->get();
        $std_ids = Student::where('campus_id', $idz)->pluck('id')->toArray();
        // $challans = FeeChallan::with('feeChallanDetails')->whereIn('student_id', $std_ids)->get();
        $challans = FeeChallanDetail::with('feeChallan', 'feeChallan.voucher')->with('student')->whereIn('student_id', $std_ids)->where('fee_month', $dates)->where('fees_type_id', $type)->get();
        return $challans;
    }

    public function fex1(Request $req)
    {
        $idz = $req->id;
        $idx = $req->idx;
        $dates = $req->date;
        $type = $req->type;
        // return FeeChallanDetail::with('feeChallanreport')->with('student')->with('studentClassreport')->where('fee_month', '2023-05-01')->where('fees_type_id','4')->get();
        $std_ids = Student::where('campus_id', $idz)->where('student_class_id', $idx)->pluck('id')->toArray();
        // $challans = FeeChallan::with('feeChallanDetails')->whereIn('student_id', $std_ids)->get();
        $challans = FeeChallanDetail::with('feeChallan', 'feeChallan.voucher')->with('student')->whereIn('student_id', $std_ids)->where('fee_month', $dates)->where('fees_type_id', $type)->get();
        return $challans;
    }
    public function fex2(Request $req)
    {
        $idz = $req->id;
        $idx = $req->idx;
        $idy = $req->idy;
        $dates = $req->date;
        $type = $req->type;
        // return FeeChallanDetail::with('feeChallanreport')->with('student')->with('studentClassreport')->where('fee_month', '2023-05-01')->where('fees_type_id','4')->get();
        $std_ids = Student::where('campus_id', $idz)->where('student_class_id', $idx)->where('global_section_id', $idy)->pluck('id')->toArray();
        // $challans = FeeChallan::with('feeChallanDetails')->whereIn('student_id', $std_ids)->get();
        $challans = FeeChallanDetail::with('feeChallan', 'feeChallan.voucher')->with('student')->whereIn('student_id', $std_ids)->where('fee_month', $dates)->where('fees_type_id', $type)->get();
        return $challans;
    }

    public function xxfeeReports(Request $req)
    {
        $std_ids = Student::where('campus_id', 1)->pluck('id')->toArray();
        $challans = FeeChallan::with('feeChallanDetails', 'campus.printAccountNos')->whereIn('student_id', $std_ids)->latest()->paginate(1000);
        $challans_for_total = FeeChallan::whereIn('student_id', $std_ids)->where('status', '<', 2)->get();
        $totalPaid = $challans_for_total->sum('paid');
        $totalPayable = $challans_for_total->sum('payable');
        $netPayable = $totalPayable - $totalPaid;

        return [
            'total payable' => $totalPayable,
            'total paid' => $totalPaid,
            'net payable' => $netPayable,
            'challans' => FeeChallanResource::collection($challans)->resource,
        ];
    }
    public function xxreports(Request $req)
    {
        $class = ClassSection::get();
        $class->load('student', 'section');
        $classes = GlobalSection::select('name')->get();
        $rxx = $req->id;
        if ($rxx == '1') {
            $hostel_fees = Student::whereNull('dob')->get();
            $hostel_fees->load('studentClass', 'globalSection');

            $data = [
                'classes' => $class,
                'hoste_fees' => $hostel_fees,

            ];

            return ['hoste_fees' => $hostel_fees, 'classes_sections' => $class];
        }
        if ($rxx == '2') {
            $hostel_fees = Student::whereNull('father_cnic')->get();
            $hostel_fees->load('studentClass', 'globalSection');

            $data = [
                'classes' => $class,
                'hoste_fees' => $hostel_fees,
            ];

            return ['hoste_fees' => $hostel_fees, 'classes_sections' => $class];
        }
        if ($rxx == '3') {
            $hostel_fees = Student::whereNull('mobile_no')->get();
            $hostel_fees->load('studentClass', 'globalSection');

            $data = [
                'classes' => $class,
                'hoste_fees' => $hostel_fees,
            ];

            return ['hoste_fees' => $hostel_fees, 'classes_sections' => $class];
        }
    }
    public function stnrpt(Request $req)
    {
        $rxx = $req->id;
        $rxx1 = $req->cmps;

        if ($rxx == '1') {
            // $hostel_fees =Student::select('admission_id','name','father_name','gender','mobile_no','dob','father_cnic','campus_id','student_class_id','global_section_id','id')->orderBy('campus_id', 'ASC')->orderBy('student_class_id', 'ASC')->orderBy('global_section_id', 'ASC')->get();
            //$hostel_fees->load('studentClass', 'globalSection','studentLiableFeesMonthly','campus');
            //return Student::with('studentClass')->with('globalSection')->with('studentLiableFeesMonthly')->with('campus')->select('admission_id','name','father_name','gender','mobile_no','dob','father_cnic','campus_id','student_class_id','global_section_id','id')->orderBy('campus_id', 'ASC')->orderBy('student_class_id', 'ASC')->orderBy('global_section_id', 'ASC')->get();
            $students = Student::with('studentLiableFeesMonthly')->where('status', '2')->orderBy('campus_id', 'ASC')->orderBy('student_class_id', 'ASC')->orderBy('global_section_id', 'ASC')->get();
            return StudentResourceShortReport::collection($students);



            // return $hostel_fees;
        }
        if ($rxx == '2') {
            $students = Student::with('studentLiableFeesMonthly')->orderBy('campus_id', 'ASC')->orderBy('student_class_id', 'ASC')->orderBy('global_section_id', 'ASC')->get();
            return StudentResourceShortReport::collection($students);
            //    return Student::with('studentClass')->with('globalSection')->with('studentLiableFeesMonthly')->with('campus')->orderBy('campus_id', 'ASC')->orderBy('student_class_id', 'ASC')->orderBy('global_section_id', 'ASC')->get();
            // $hostel_fees->load('studentClass', 'globalSection','studentLiableFeesMonthly','campus');



            //return $hostel_fees;
        }
        if ($rxx == '3') {
            $students = Student::with('studentLiableFeesMonthly')->where('campus_id', $rxx1)->where('status', '2')->orderBy('student_class_id', 'ASC')->orderBy('global_section_id', 'ASC')->get();
            return StudentResourceShortReport::collection($students);
            // return Student::with('studentClass')->with('globalSection')->with('studentLiableFeesMonthly')->with('campus')->select('admission_id','name','father_name','gender','mobile_no','dob','father_cnic','campus_id','student_class_id','global_section_id','id')->where('campus_id',$rxx1)->where('status','2')->orderBy('campus_id', 'ASC')->orderBy('student_class_id', 'ASC')->orderBy('global_section_id', 'ASC')->get();
            // $hostel_fees->load('studentClass', 'globalSection','studentLiableFeesMonthly','campus');
            // return $hostel_fees;
        }
    }
    function allstn(Request $req)
    {

        $hosts = Employee::select('emp_code', 'full_name', 'father_name', 'gender', 'mobile_no', 'dob', 'designation_id', 'qualification', 'campus_id')->orderBy('campus_id', 'ASC')->get();
        $hosts->load('designation', 'campus');
        return $hosts;
    }
}
