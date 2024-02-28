<?php

namespace App\Http\Controllers\Api\SMS;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\SMS\StudentCustomSmsRequest;
use App\Jobs\SendMessageJob;
use App\Models\Student;
use Illuminate\Support\Facades\Config;

class StudentCustomSMSController extends BaseController
{
    public function studentCustomSMS(StudentCustomSmsRequest $request)
    {
        $students = Student::whereIn('id', $request->student_ids)
            ->get();

        foreach ($students as $student) {
            // if (_campusAllowedMessages($student->campus_id) > 0){
            SendMessageJob::dispatch(5, $student->mobile_no, $request->message);
            // _campusMessageDecrement($student->campus_id);
            // }else{
            //     return $this->sendError('You have insufficient no of messages');
            // }
        }

        return $this->sendResponse([], 'Message sent successfully');
    }
}
