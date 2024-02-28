<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeeChallanDetail;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StudentFeeStatusController extends BaseController
{
    public function CampusViseFeeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:sessions,id',
            'campus_id'  => 'required|exists:campuses,id',
            'date' => 'required',
            'fee_type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $session_id = $request->session_id;
        $campus_id = $request->campus_id;
        $date = $request->date;
        $fee_type = $request->fee_type;
        // return FeeChallanDetail::with('feeChallanreport')->with('student')->with('studentClassreport')->where('fee_month', '2023-05-01')->where('fees_type_id','4')->get();
        $student_ids = Student::where('campus_id', $campus_id)
            ->where('session_id', $session_id)
            ->pluck('id')->toArray();
        // $challans = FeeChallan::with('feeChallanDetails')->whereIn('student_id', $std_ids)->get();
        $fee_challans = FeeChallanDetail::with('feeChallan', 'feeChallan.voucher')
            ->with('student')
            ->whereIn('student_id', $student_ids)
            ->where('fee_month', $date)
            ->where('fees_type_id', $fee_type)->get();
        // return $challans;
        return $this->sendResponse($fee_challans, '', 200);
    }
    public function ClassViseFeeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:sessions,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'campus_id'  => 'required|exists:campuses,id',
            'date' => 'required',
            'fee_type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $session_id = $request->session_id;
        $student_class_id = $request->student_class_id;
        $campus_id = $request->campus_id;
        $date = $request->date;
        $fee_type = $request->fee_type;
        // return FeeChallanDetail::with('feeChallanreport')->with('student')->with('studentClassreport')->where('fee_month', '2023-05-01')->where('fees_type_id','4')->get();
        $student_ids = Student::where('campus_id', $campus_id)
            ->where('session_id', $session_id)
            ->where('student_class_id', $student_class_id)
            ->pluck('id')->toArray();
        // $challans = FeeChallan::with('feeChallanDetails')->whereIn('student_id', $std_ids)->get();
        $fee_challans = FeeChallanDetail::with('feeChallan', 'feeChallan.voucher')
            ->with('student')
            ->whereIn('student_id', $student_ids)
            ->where('fee_month', $date)
            ->where('fees_type_id', $fee_type)->get();
        // return $challans;
        return $this->sendResponse($fee_challans, '', 200);
    }
    public function SectionViseFeeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:sessions,id',
            'global_section_id' => 'nullable|exists:global_sections,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'campus_id'  => 'required|exists:campuses,id',
            'date' => 'required',
            'fee_type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $session_id = $request->session_id;
        $global_section_id = $request->global_section_id;
        $student_class_id = $request->student_class_id;
        $campus_id = $request->campus_id;
        $date = $request->date;
        $fee_type = $request->fee_type;
        // return FeeChallanDetail::with('feeChallanreport')->with('student')->with('studentClassreport')->where('fee_month', '2023-05-01')->where('fees_type_id','4')->get();
        $student_ids = Student::where('campus_id', $campus_id)
            ->where('session_id', $session_id)
            ->where('student_class_id', $student_class_id)
            ->where('global_section_id', $global_section_id)
            ->pluck('id')->toArray();
        // $challans = FeeChallan::with('feeChallanDetails')->whereIn('student_id', $std_ids)->get();
        $fee_challans = FeeChallanDetail::with('feeChallan', 'feeChallan.voucher')
            ->with('student')
            ->whereIn('student_id', $student_ids)
            ->where('fee_month', $date)
            ->where('fees_type_id', $fee_type)->get();
        // return $challans;
        return $this->sendResponse($fee_challans, '', 200);
    }
    public function YearlyFeeReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Start_Date' => 'required',
            'End_Date' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $Start_Date = $request->Start_Date;
        $End_Date = $request->End_Date;
        $Fee_Challan = FeeChallanDetail::select('fee_challan_details.student_id', DB::raw('count(fee_challan_details.student_id) as months'), DB::raw('sum(fee_challan_details.amount) as total'))
            ->leftJoin('fee_challans', 'fee_challans.id', '=', 'fee_challan_details.fee_challan_id')
            ->where('fee_challan_details.fees_type_id', 4)
            ->where('fee_challans.status', 2)->whereBetween('fee_challan_details.fee_month', [$Start_Date, $End_Date])
            ->groupBy('fee_challan_details.student_id')
            ->having(DB::raw('COUNT(fee_challan_details.student_id)'), '>', '6')
            ->get();
        $Fee_Challan->load('student.studentClass', 'student.globalSection', 'student.studentLiableFees', 'student.feeChallanDetailsLast');
        //  $hostel_fees= DB::table('fee_challans')
        //            ->leftJoin('fee_challan_details', 'fee_challans.id', '=', 'fee_challan_details.fee_challan_id')// joining the contacts table , where user_id and contact_user_id are same
        //        ->select('fee_challans.student_id', DB::raw('SUM(fee_challan_details.amount) as total'), 'fee_challan_details.fee_month','fee_challan_details.amount')
        //     ->where('fee_challan_details.fees_type_id','=','4')
        //    ->groupBy('fee_challan_details.student_id')
        //   ->get();
        return $this->sendResponse($Fee_Challan, '', 200);
        //return $Fee_Challan;
    }
}
