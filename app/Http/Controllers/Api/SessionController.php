<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ActivateSessionRequest;
use App\Http\Resources\SessionResource;
use App\Http\Resources\StudentClassResource;
use App\Http\Resources\SubjectResource;
use App\Models\Attendance;
use App\Models\DateSheet;
use App\Models\Result;
use App\Models\Session;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Repository\SessionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SessionController extends BaseController
{
    public function __construct(SessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse($this->sessionRepository->index(), []);
    }

    public function show(Session $session)
    {
        return $this->sendResponse($this->sessionRepository->show($session), []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|string|unique:sessions',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->sessionRepository->store($request), []);
    }

    public function activateSession(ActivateSessionRequest $request)
    {

        if ($request->academic_year){

            Session::where('active_academic_year',1)->update([
                'active_academic_year' => 0
            ]);

            Session::where('id',$request->year_id)
                        ->update([
                        'active_academic_year' => 1
                        ]);
        }

        if ($request->financial_year){

            Session::where('active_financial_year',1)->update([
                'active_financial_year' => 0
            ]);

            Session::where('id',$request->year_id)
                ->update([
                    'active_financial_year' => 1
                ]);
        }

        $session = Session::find($request->year_id);

        $response = new SessionResource($session);

        return $this->sendResponse($response, []);
    }

    public function examsSessionsList(){
        $student = _user()->student;
        $data = [];

        if ($student){
            $subject_ids = Result::where('student_id',$student->id)->pluck('subject_id')->unique()->toArray();
            $session_ids = Result::where('student_id',$student->id)->pluck('session_id')->unique()->toArray();
            $session_ids[] = Session::where('active_academic_year',1)->first()->id;
            $classes_ids = Result::where('student_id',$student->id)->pluck('student_class_id')->unique()->toArray();
            $classes_ids[] = $student->student_class_id;

            $data = [
              'student_exam_sessions_list' => SessionResource::collection(Session::whereIn('id',$session_ids)->get()),
              'student_exam_subjects_list' => SubjectResource::collection(Subject::whereIn('id',$subject_ids)->get()),
              'student_exam_classes_list' => StudentClassResource::collection(StudentClass::whereIn('id',$classes_ids)->get()),
            ];
        }

        return $this->sendResponse($data, []);
    }
}
