<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StudentReport\FeesRequest;
use App\Http\Resources\StudentResource;
use App\Models\FeeChallan;
use App\Models\FeeChallanDetail;
use App\Models\Student;

class FeesController extends BaseController
{
    public function paidPendingStudents(FeesRequest $request)
    {
        $month = explode("-", $request->date);

        $challan_ids = FeeChallanDetail::whereMonth('fee_month',$month[1])->pluck('fee_challan_id')->unique();

        $total_amount = FeeChallan::whereIn('id',$challan_ids)->sum('payable');

        $paid_amount = FeeChallan::whereIn('id',$challan_ids)
            ->where('status','!=',0)
            ->sum('paid');

        $paid_students_ids = FeeChallan::whereIn('id',$challan_ids)
            ->where('status','!=',0)
            ->pluck('student_id')->unique();

        $paid_student_list = Student::whereIn('id',$paid_students_ids)
            ->with(['feeChallans.feeChallanDetails','feeChallans'])->get();

        $unpaid_amount = FeeChallan::whereIn('id',$challan_ids)
            ->where('status','=',0)
            ->sum('payable');

        $unpaid_students_ids = FeeChallan::whereIn('id',$challan_ids)
            ->where('status','=',0)
            ->pluck('student_id')->unique();

        $unpaid_student_list = Student::whereIn('id',$unpaid_students_ids)
            ->with(['feeChallans.feeChallanDetails','feeChallans'])->get();

        $data = [
            'paid' =>  $paid_amount,
            'unpaid' =>  $unpaid_amount,
            'total' =>  $total_amount,
            'paid_students' => StudentResource::collection($paid_student_list),
            'unpaid_students' => StudentResource::collection($unpaid_student_list),
        ];
        return $this->sendResponse($data, [], 200);
    }
}
