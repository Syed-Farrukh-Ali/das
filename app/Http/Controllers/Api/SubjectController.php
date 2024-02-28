<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StudentClassSubjectRequest;
use App\Http\Resources\StudentResource;
use App\Http\Resources\SubjectResource;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubjectController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse(SubjectResource::collection(Subject::all()), []);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\Response
     */
    public function show(Subject $subject)
    {
        return $this->sendResponse(new SubjectResource($subject), []);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\Response
     */
    public function subjectsAssignStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_ids.*' => 'required|integer|exists:students,id',
            'subject_ids.*' => 'nullable|integer|exists:subjects,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
            // code...
        }
        $students = Student::find($request->student_ids);
        foreach ($students as $key => $student) {
            $student->subjects()->detach();
            $student->subjects()->sync($request->subject_ids);
        }
        $students->load('subjects');

        return $this->sendResponse(StudentResource::collection($students), []);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\Response
     */
    public function subjectsAssignStudentWithoutDetach(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_ids.*' => 'required|integer|exists:students,id',
            'subject_ids.*' => 'nullable|integer|exists:subjects,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
            // code...
        }
        $students = Student::find($request->student_ids);
        foreach ($students as $key => $student) {
            $student->subjects()->syncWithoutDetaching($request->subject_ids);
        }
        $students->load('subjects');

        return $this->sendResponse(StudentResource::collection($students), []);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Subject $subject)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\Response
     */
    public function destroy(Subject $subject)
    {
        //
    }

    public function subjectAssignToClasses(StudentClassSubjectRequest $request){
        $class = StudentClass::find($request->class_id);

        $class->subjects()->sync($request->subject_ids);

        $message = $class->name.' class subject assigned successfully';

        $response = SubjectResource::collection($class->subjects);

        return $this->sendResponse($response,$message);
    }

    public function assignedSubjectOfClass($class_id){
        $class = StudentClass::find($class_id);

        $response = SubjectResource::collection($class->subjects);

        return $this->sendResponse($response,[]);
    }

    public function removeAssignedSubjects(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_ids.*' => 'required|integer|exists:students,id',
            'subject_ids.*' => 'nullable|integer|exists:subjects,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $students = Student::find($request->student_ids);
        foreach ($students as $student) {
            $student->subjects()->detach($request->subject_ids);
        }
        $students->load('subjects');

        return $this->sendResponse(StudentResource::collection($students), []);
    }
}
