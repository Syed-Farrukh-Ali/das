<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResourceShortReport;
use App\Models\FeeChallan;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StudentFeeInformationController extends Controller
{
    public function showcampusfeeinfomation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id'  => 'required|exists:sessions,id',
            'campus_ids' => 'nullable|integer|exists:campuses,id',
            'report_type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $report_type = $request->report_type;
        $campus_id = $request->campus_id;
        $year_id = $request->session_id;
        $studentCollectionData = [];
        // $statuses="";
        // switch ($report_type){
        // case "active_student":
        //     $statuses="1";
        // break;
        //     case "all_student":
        //         $statuses = "0";
        //         break;

        // }
        if ($report_type == 'active_student') {
            // $hostel_fees =Student::select('admission_id','name','father_name','gender','mobile_no','dob','father_cnic','campus_id','student_class_id','global_section_id','id')->orderBy('campus_id', 'ASC')->orderBy('student_class_id', 'ASC')->orderBy('global_section_id', 'ASC')->get();
            //$hostel_fees->load('studentClass', 'globalSection','studentLiableFeesMonthly','campus');
            //return Student::with('studentClass')->with('globalSection')->with('studentLiableFeesMonthly')->with('campus')->select('admission_id','name','father_name','gender','mobile_no','dob','father_cnic','campus_id','student_class_id','global_section_id','id')->orderBy('campus_id', 'ASC')->orderBy('student_class_id', 'ASC')->orderBy('global_section_id', 'ASC')->get();
            $students = Student::with('studentLiableFeesMonthly')
                ->where('status', '2')
                ->where('session_id', $year_id)
                ->orderBy('campus_id', 'ASC')
                ->orderBy('student_class_id', 'ASC')
                ->orderBy('global_section_id', 'ASC')->get();
            $studentCollectionData = StudentResourceShortReport::collection($students);



            // return $hostel_fees;
        }
        if ($report_type == 'all_student') {
            $students = Student::with('studentLiableFeesMonthly')
                ->where('session_id', $year_id)
                ->orderBy('campus_id', 'ASC')
                ->orderBy('student_class_id', 'ASC')
                ->orderBy('global_section_id', 'ASC')->get();
            $studentCollectionData = StudentResourceShortReport::collection($students);
            //    return Student::with('studentClass')->with('globalSection')->with('studentLiableFeesMonthly')->with('campus')->orderBy('campus_id', 'ASC')->orderBy('student_class_id', 'ASC')->orderBy('global_section_id', 'ASC')->get();
            // $hostel_fees->load('studentClass', 'globalSection','studentLiableFeesMonthly','campus');



            //return $hostel_fees;
        }
        if ($report_type == 'campus_student') {
            $students = Student::with('studentLiableFeesMonthly')
                ->where('session_id', $year_id)
                ->where('campus_id', $campus_id)
                ->where('status', '2')
                ->orderBy('student_class_id', 'ASC')
                ->orderBy('global_section_id', 'ASC')->get();
            $studentCollectionData = StudentResourceShortReport::collection($students);
            // return Student::with('studentClass')->with('globalSection')->with('studentLiableFeesMonthly')->with('campus')->select('admission_id','name','father_name','gender','mobile_no','dob','father_cnic','campus_id','student_class_id','global_section_id','id')->where('campus_id',$rxx1)->where('status','2')->orderBy('campus_id', 'ASC')->orderBy('student_class_id', 'ASC')->orderBy('global_section_id', 'ASC')->get();
            // $hostel_fees->load('studentClass', 'globalSection','studentLiableFeesMonthly','campus');
            // return $hostel_fees;
        }
        return $this->sendResponse($studentCollectionData, '', 200);
    }
    public function showAllChallans() // unpaid
    {
        // code...
        $challans = FeeChallan::select([DB::raw("SUM(payable) as netpay,bank_account_id")])->where('status', 1)->groupBy('bank_account_id')->get();
        $challans_for_total = FeeChallan::where('status', 1)->get();
        $challans->load('bank_account');
        $totalPayable = $challans_for_total->sum('payable');
        $data = [
            'total_payable' => $totalPayable,
            'challan' => $challans,


        ];

        return $data;
    }
}
