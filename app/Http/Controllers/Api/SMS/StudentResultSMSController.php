<?php

namespace App\Http\Controllers\Api\SMS;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\SMS\StudentResultRequest;
use App\Http\Resources\StudentResourcePure;
use App\Jobs\SendMessageJob;
use App\Models\Campus;
use App\Models\Exam;
use App\Models\GlobalSection;
use App\Models\Result;
use App\Models\Student;
use App\Models\StudentClass;
use Illuminate\Support\Facades\Config;

class StudentResultSMSController extends BaseController
{
    public function studentResultSMS(StudentResultRequest $request)
    {

        $student = Student::where('id',$request->student_ids[0])
            ->get(['id','campus_id','student_class_id','global_section_id']);

        $campus_id = $student[0]->campus_id;

        $student_class_id = $student[0]->student_class_id;
        $global_section_id = $student[0]->global_section_id;

        $exam = Exam::with('exam_type')->find($request->exam_id);
        $results = Result::orderBy('sequence', 'ASC')->where([
            'exam_id' => $request->exam_id,
            'campus_id' => $campus_id,
            'student_class_id' => $student_class_id,
            'global_section_id' => $global_section_id,
        ])->get(['student_id', 'full_marks', 'gain_marks', 'practical_marks', 'percentage', 'grade', 'subject_id']);

        $student_ids = $results->pluck('student_id')->unique()->toArray();

        foreach ($request->student_ids as $key => $student_id) {
            $student = Student::find($student_id, ['id', 'name', 'mobile_no']);
            $student_result = $results->where('student_id', $student->id);
            $student_result->load('subject')->toArray();
            $subject_count = $student_result->count();
            if (!$subject_count) {
                continue;
            }

            $full_marks = $student_result->sum('full_marks');
            $gain_marks = $student_result->sum('gain_marks') + $student_result->sum('practical_marks');

            $subjects = '';

            foreach($student_result as $result)
            {
                $subjects = $subjects.$result->subject->name.':'.$result->gain_marks.'/'.$result->gain_marks."\n";
            }

            $message = 'Dear Parents/Guardian, '.$student->name."\n".$exam->exam_type->name.' Result is '.
                "\n".$subjects."\n".' Result: '.$gain_marks.'/'.$full_marks.'. ';

            if (_campusAllowedMessages($student->campus_id) > 0){
                SendMessageJob::dispatch(3,$student->mobile_no,$message);
                _campusMessageDecrement($student->campus_id);
            }else{
                return $this->sendError('You have insufficient no of messages');
            }
        }

        return $this->sendResponse([],'Message sent successfully');

    }
}
