<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\EmpLectureResource;
use App\Models\EmpLecture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmpLectureController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function empLectureList($employee_id)
    {
        $validator = Validator::make(['employee_id' => $employee_id], [

            'employee_id' => 'required|exists:employees,id',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $emp_lectures = EmpLecture::where('employee_id', $employee_id)->get();
        $emp_lectures->load('campus', 'session', 'student_class', 'global_section', 'subject');

        return $this->sendResponse(EmpLectureResource::collection($emp_lectures), []);
    }

    public function empLectureAssign(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'employee_id' => 'required|exists:employees,id',
            'campus_ids.*' => 'required|exists:campuses,id',
            'year_ids.*' => 'required|exists:sessions,id',
            'student_class_ids.*' => 'required|exists:student_classes,id',
            'global_section_ids.*' => 'required|exists:global_sections,id',
            'subject_ids.*' => 'nullable|exists:subjects,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        foreach ($request->subject_ids as $key => $subject_id) {
            $emp_lectures = EmpLecture::create([
                'employee_id' => $request->employee_id,
                'campus_id' => $request->campus_ids[$key],
                'session_id' => $request->year_ids[$key],
                'student_class_id' => $request->student_class_ids[$key],
                'global_section_id' => $request->global_section_ids[$key],
                'subject_id' => $request->subject_ids[$key],
            ]);
        }
        $emp_lectures = EmpLecture::where('employee_id', $request->employee_id)->get();
        $emp_lectures->load('employee', 'campus', 'session', 'student_class', 'global_section', 'subject');

        return $this->sendResponse(EmpLectureResource::collection($emp_lectures), []);
    }

    public function empAssignToClass(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'year_id' => 'required|exists:sessions,id',
            'campus_id' => 'required|exists:campuses,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'global_section_id' => 'required|exists:global_sections,id',
            'subject_ids.*' => 'nullable|exists:subjects,id',
            'employee_ids.*' => 'required|exists:employees,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        foreach ($request->subject_ids as $key => $subject_id) {
            $emp_lectures = EmpLecture::create([
                'session_id' => $request->year_id,
                'campus_id' => $request->campus_id,
                'student_class_id' => $request->student_class_id,
                'global_section_id' => $request->global_section_id,
                'employee_id' => $request->employee_ids[$key],
                'subject_id' => $request->subject_ids[$key],
            ]);
        }
        $class_lectures = EmpLecture::where([
            'session_id' => $request->year_id,
            'campus_id' => $request->campus_id,
            'student_class_id' => $request->student_class_id,
            'global_section_id' => $request->global_section_id,
        ])->get();
        $class_lectures->load('employee', 'campus', 'session', 'student_class', 'global_section', 'subject');

        return $this->sendResponse(EmpLectureResource::collection($class_lectures), []);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function classLectureList(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'year_id' => 'required|exists:sessions,id',
            'campus_id' => 'required|exists:campuses,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'global_section_id' => 'required|exists:global_sections,id',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $class_lectures = EmpLecture::where([
            'session_id' => $request->year_id,
            'campus_id' => $request->campus_id,
            'student_class_id' => $request->student_class_id,
            'global_section_id' => $request->global_section_id,
        ])->get();
        $class_lectures->load('employee', 'campus', 'session', 'student_class', 'global_section', 'subject');

        return $this->sendResponse(EmpLectureResource::collection($class_lectures), []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmpLecture  $empLecture
     * @return \Illuminate\Http\Response
     */
    public function show(EmpLecture $empLecture)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EmpLecture  $empLecture
     * @return \Illuminate\Http\Response
     */
    public function edit(EmpLecture $empLecture)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EmpLecture  $empLecture
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmpLecture $empLecture)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmpLecture  $empLecture
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmpLecture $empLecture)
    {
        $empLecture->delete();

        return $this->sendResponse('employee lecture successfully deleted', []);
    }
}
