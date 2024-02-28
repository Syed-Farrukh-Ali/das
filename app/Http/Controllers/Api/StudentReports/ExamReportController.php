<?php

namespace App\Http\Controllers\Api\StudentReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StudentReport\ExamReportRequest;
use App\Models\Exam;
use App\Models\Result;
use App\Models\Subject;

class ExamReportController extends BaseController
{
    public function report(ExamReportRequest $request)
    {
        $user = _user();
        $student = $user->student;
        $exam_ids = Result::where('student_id',$student->id)
            ->where('session_id',$request->year_id)
            ->where('student_class_id',$request->student_class_id)
            ->when($request->subject_id, function ($query) use ($request) {
                return $query->where('subject_id', $request->subject_id);
            })
            ->pluck('exam_id')
            ->toArray();

        $exams = Exam::whereIn('id',$exam_ids)->get();

        $data = [];

        foreach ($exams as $exam)
        {
            if ($request->subject_id){
                $result = Result::where('student_id',$student->id)
                    ->where('student_class_id',$request->student_class_id)
                    ->where('session_id',$request->year_id)
                    ->where('subject_id',$request->subject_id)
                    ->where('exam_id',$exam->id)
                    ->first();
            }else{
                $total_marks = Result::where('student_id',$student->id)
                    ->where('student_class_id',$request->student_class_id)
                    ->where('session_id',$request->year_id)
                    ->where('exam_id',$exam->id)
                    ->sum('full_marks');

                $gain_marks = Result::where('student_id',$student->id)
                    ->where('student_class_id',$request->student_class_id)
                    ->where('session_id',$request->year_id)
                    ->where('exam_id',$exam->id)
                    ->sum('gain_marks');

                if ($total_marks > 0){
                    $percentage = $gain_marks/$total_marks*100;
                }else{
                    $percentage = 0;
                }

                $result = [
                  'total_marks' => $total_marks,
                  'gain_marks' => $gain_marks,
                  'percentage' => round($percentage,2),
                ];
            }

            $exam_data = [
                'exam' => $exam->exam_type->name,
                'result' => $result,
            ];

            $data[] = $exam_data;
        }

        return $this->sendResponse($data, '',200);
    }
}
