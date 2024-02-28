<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AttendanceResource;
use App\Jobs\SendMessageJob;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function markAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_ids.*' => 'required|exists:students,id',
            'date' => 'date|date_format:Y-m-d',
            'attendance_status_id' => 'nullable|exists:attendance_statuses,id',
            'mobile_sms' => 'required|boolean',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }


        $students = Student::whereIn('id', $request->student_ids)->get();
        $attendance_status = null;
        if ($request->attendance_status_id) {
            $attendance_status = AttendanceStatus::find($request->attendance_status_id);
        }
        foreach ($students as $key => $student) {

            if ($request->message) {
                $message = $request->message;
            } else {
                $message = '';
            }

            if ($attendance_status) {

                Attendance::updateOrCreate([
                    'student_id' => $student->id,
                    'date' => $request->date,
                ], [
                    'campus_id' => $student->campus_id,
                    'student_class_id' => $student->student_class_id,
                    'global_section_id' => $student->global_section_id,

                    'attendance_status_id' => $request->attendance_status_id,
                    'status_name' => $attendance_status->name ?? '',

                ]);

                switch ($attendance_status->name) {
                    case ('Absent'):
                        $message = 'Dear Parents ' . $student->name . ' is absent from school today with out any information. Kindly contact us.';
                        break;

                    case ('Sick'):
                        $message = 'Dear Parents ' . $student->name . ' may get well soon. We hope that he/she will attend the school at the earliest.';
                        break;

                    case ('Leave'):
                        $message = 'Dear Parents Leave Application of ' . $student->name . ' has been received in the school office and approved.';
                        break;

                    case ('Home work not done'):
                        $message = 'Dear Parents your child ' . $student->name . ' home work is incomplete. Kindly pay attention on this matter.';
                        break;

                    case ('Improper uniform'):
                        $message = 'Dear Parents your child ' . $student->name . ' is not in proper uniform. Kindly pay attention on this matter.';
                        break;

                    case ('Test not prepared'):
                        $message = 'Dear Parents your child ' . $student->name . ' test was not prepared. Kindly pay attention on this matter. ';
                        break;
                }
            }

            if ($request->mobile_sms) {

                // if (_campusAllowedMessages($student->campus_id) > 0){
                SendMessageJob::dispatch(2, $student->mobile_no, $message);
                //         _campusMessageDecrement($student->campus_id);
                //     }
                // }else{
                //     return $this->sendError('You have insufficient no of messages');
            }
        }

        $attendance_status_name = $attendance_status->name ?? '';

        return $this->sendResponse([], "student marked " . $attendance_status_name, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function singleDayAttendances(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campus_id' => 'required|exists:campuses,id',
            'student_class_id' => 'nullable|exists:student_classes,id',
            'global_section_id' => 'nullable|exists:global_sections,id',
            'date' => 'date|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        $student_class_id = $request->student_class_id;
        $global_section_id = $request->global_section_id;

        $attendances = Attendance::with('student.studentClass', 'student.globalSection', 'attendance_status')->where('campus_id', $request->campus_id)
            ->where('date', $request->date)
            ->where(function ($query) use ($student_class_id) {
                return $student_class_id ? $query->where('student_class_id', '=', $student_class_id) : '';
            })
            ->where(function ($query) use ($global_section_id) {
                return $global_section_id ? $query->where('global_section_id', '=', $global_section_id) : '';
            })
            ->get();

        $data = [
            'total' => $attendances->count(),
            'attendances' => AttendanceResource::collection($attendances),
        ];

        return $this->sendResponse($data, 'attendaces of the day', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function studentAttendance()
    {
        $student = Auth()->user()->student;
        // $student = Student::find(2);
        $attendances = $student->attendances;
        $data = [
            'attendance' => AttendanceResource::collection($attendances),
            'total_absent' => $attendances->where('attendance_status_id', 1)->count(),
            'total_sick' => $attendances->where('attendance_status_id', 2)->count(),
            'total_leave' => $attendances->where('attendance_status_id', 3)->count(),
            'total_late_coming' => $attendances->where('attendance_status_id', 4)->count(),
            'total_home_work_not_done' => $attendances->where('attendance_status_id', 5)->count(),
            'total_improper_uniform' => $attendances->where('attendance_status_id', 6)->count(),
            'total_test_not_prepared' => $attendances->where('attendance_status_id', 7)->count(),
        ];

        return $this->sendResponse($data, [], 200);
    }


    public function update(Request $request, Attendance $attendance)
    {
        //
    }


    public function destroy(Attendance $attendance)
    {
        //
    }
}
