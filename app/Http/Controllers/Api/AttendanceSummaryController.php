<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceSummaryController extends BaseController
{
    public function SingleStudentAttendanceDetails(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'admission_id'  => 'required|exists:students,admission_id',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $admission_id = $req->admission_id;
        $start_date = $req->start_date;
        $end_date = $req->end_date;
        $student_id = Student::where('admission_id', $admission_id)->pluck('id');
        $Student_attendance = Attendance::where('student_id', $student_id)
            ->whereBetween('date', [$start_date, $end_date])
            ->orderBy('attendance_status_id')->get();
        $student_data = Student::where('admission_id', $admission_id)->get();
        $student_data->load('studentClass', 'globalSection', 'campus');
        // return ['attendance' => $Student_attendance, 'stu' => $student_data];
        return $this->sendResponse(['attendance_data' => $Student_attendance, 'student_data' => $student_data], '', 200);
    }
    public function MonthlyAttendance(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'campus_id' => 'required|exists:campuses,id',
            'student_class_id' => 'required|exists:students,student_class_id',
            'global_section_id' => 'required|exists:students,global_section_id',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $campus_id = $req->campus_id;
        $class_id = $req->student_class_id;
        $section_id = $req->global_section_id;
        $start_date = $req->start_date;
        $end_date = $req->end_date;
        $year_id = $req->year_id;
        $student_id = Student::where('session_id', $year_id)
            ->where('campus_id', $campus_id)
            ->where('student_class_id', $class_id)
            ->where('global_section_id', $section_id)
            ->pluck('id')
            ->toArray();
        $attandence_data = Attendance::selectRaw('ifnull(count(student_id),0) as count,student_id,attendance_status_id')
            ->whereIn('attendance_status_id', [1, 2, 3, 4, 5, 6, 7])
            ->whereIn('student_id', $student_id)
            ->whereBetween('date', [$start_date, $end_date])
            ->groupBy('student_id')->groupBy('attendance_status_id')->get();
        $attandance_student_data = Attendance::with('student')
            ->select('student_id')
            ->where('campus_id', $campus_id)
            ->where('student_class_id', $class_id)
            ->where('global_section_id', $section_id)
            ->groupBy('student_id')->get();
        // return ['attendance' => $attandence_data, 'student_data' => $attandance_student_data];
        return $this->sendResponse(['attendance' => $attandence_data, 'student_data' => $attandance_student_data], '', 200);
    }
    public function SingleStudentMonthlyAttendance(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'admission_id'  => 'required|exists:students,admission_id',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $admission_id = $req->admission_id;
        $start_date = $req->start_date;
        $end_date = $req->end_date;
        $student_id = Student::where('admission_id', $admission_id)->pluck('id');
        $attandence_data = Attendance::selectRaw('ifnull(count(student_id),0) as count,student_id,attendance_status_id')->whereIn('attendance_status_id', [1, 2, 3, 4, 5, 6, 7])->where('student_id', $student_id)->whereBetween('date', [$start_date, $end_date])->groupBy('student_id')->groupBy('attendance_status_id')->get();
        $attandance_student_data = Attendance::with('student')->select('student_id')->where('student_id', $student_id)->groupBy('student_id')->get();
        return $this->sendResponse(['attendance' => $attandence_data, 'student_data' => $attandance_student_data], '', 200);
    }
}
