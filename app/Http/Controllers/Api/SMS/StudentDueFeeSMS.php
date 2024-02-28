<?php

namespace App\Http\Controllers\Api\SMS;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\SMS\StudentDueFeeSMSRequest;
use App\Http\Resources\StudentResourcePure;
use App\Jobs\SendMessageJob;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class StudentDueFeeSMS extends BaseController
{
    public function studentDueFeeSMS(Request $request)
    {
        foreach ($request->student_ids as $student_data) {

            $student = Student::find($student_data['studentId']);

            $student->load(['feeChallans.feeChallanDetails', 'feeChallans']);

            $fee_months = $student_data['months'];
            $month_names = '';
            $total_fee = $student->feeChallans()->with(['feeChallanDetails' => function ($query) use ($fee_months) {
                $query->whereIn('fee_month', $fee_months);
            }])->where('status', 0)->sum('payable');

            foreach ($fee_months as $month) {
                $month_names = $month_names . Carbon::createFromFormat('Y-m-d', $month)->format('M') . ','; //getting only month
            }

            $message = 'We have been checking our accounts and found that there is some due fee of ' . $student->name . ' (' . $student->admission_id . ') for the month of ' . $month_names . ' Rs.' . $total_fee . '. Please pay ASAP.';

            // if (_campusAllowedMessages($student->campus_id) > 0){
            SendMessageJob::dispatch(5, $student->mobile_no, $message);
            //     _campusMessageDecrement($student->campus_id);
            // }else{
            //     return $this->sendError('You have insufficient no of messages');
            // }
        }

        return $this->sendResponse([], 'Message sent successfully');
    }
}
