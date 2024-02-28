<?php

namespace App\Http\Controllers\Api\Exam;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\DateSheet\DateSheetResource;
use App\Http\Resources\ExamResource;
use App\Models\DateSheet;
use App\Models\DateSheetSubject;
use App\Models\Exam;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DateSheetController extends BaseController
{
    public function __construct()
    {
        $this->middleware('school')->only(['store', 'updateDateSheet', 'destroy']);
        // $this->middleware('subscribed')->except('store');
    }

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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:exams,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'note' => 'nullable|string',
            'dates.*' => '|date|date_format:Y-m-d',
            'subject_ids.*' => 'nullable|exists:subjects,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $exam = Exam::find($request->exam_id);

        $date_sheet_exist = DateSheet::where('student_class_id',$request->student_class_id)
                            ->where('exam_id',$request->exam_id)
                            ->first();

        if ($date_sheet_exist){
            $date_sheet_exist->update([
                'exam_type_id' => $exam->exam_type_id,
                'campus_id' => $exam->campus_id,
                'student_class_id' => $request->student_class_id,
                'note' => $request->note,
            ]);

            foreach ($request->dates as $key => $date) {
                $date_sheet_exist->dateSheetSubjects()->create([
                    'subject_id' => $request->subject_ids[$key],
                    'date' => $request->dates[$key],
                ]);
            }
        }else{
            $date_sheet = $exam->date_sheets()->create([
                'exam_type_id' => $exam->exam_type_id,
                'campus_id' => $exam->campus_id,
                'student_class_id' => $request->student_class_id,
                'note' => $request->note,
            ]);

            foreach ($request->dates as $key => $date) {
                $date_sheet->dateSheetSubjects()->create([
                    'subject_id' => $request->subject_ids[$key],
                    'date' => $request->dates[$key],
                ]);
            }
        }

        return $this->sendResponse(DateSheetResource::collection($exam->date_sheets->load('student_class', 'dateSheetSubjects','exam')), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DateSheet  $dateSheet
     * @return \Illuminate\Http\Response
     */
    public function examClassDatesheet(Request $request)//get datesheet for a class
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:exams,id',
            'student_class_id' => 'required|exists:student_classes,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $exam = Exam::find($request->exam_id);
         try {
      //  $date_sheet = $exam->date_sheets()->where('student_class_id', $request->student_class_id)->first();
      $date_sheet = $exam->date_sheets()->where('student_class_id', $request->student_class_id)
                    ->where('campus_id',$request->campus_id)
                    ->first();
        $date_sheet->load('student_class', 'dateSheetSubjects','exam');

        return $this->sendResponse(new DateSheetResource($date_sheet), []);
         }catch (\Throwable $throwable){
            return $this->sendError('Internel Server Error', [], 500);
        }
    }

    public function DateSheetExamList(Request $request){

        $validate = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'year_id' => 'required|integer|exists:sessions,id',
            'student_class_id' => 'required|integer|exists:student_classes,id'

        ]);
        if ($validate->fails()) {
            return $this->sendError($validate->errors(), [], 422);
        }

        $student = Student::find($request->student_id);

        $exam_ids = DateSheet::where('campus_id',$student->campus_id)
                        ->where('student_class_id',$request->student_class_id)
                        ->pluck('exam_id')
                        ->unique()
                        ->toArray();

        $exams = Exam::whereIn('id', $exam_ids)
                        ->where('date_sheet_status',1)
                        ->where('session_id',$request->year_id)
                        ->get()
                        ->load('session', 'exam_type');

        return $this->sendResponse(ExamResource::collection($exams), []);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DateSheet  $dateSheet
     * @return \Illuminate\Http\Response
     */
    public function edit(DateSheet $dateSheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DateSheet  $dateSheet
     * @return \Illuminate\Http\Response
     */
    public function updateDateSheet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids.*' => 'required|exists:date_sheets,id',
            'dates.*' => 'required|date|date_format:Y-m-d',
            'subject_ids.*' => 'nullable|exists:subjects,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        foreach ($request->ids as $key => $id) {
            $date_sheet = DateSheet::where('id', $id)->update([
                'date' => $request->dates[$key],
                'subject_id' => $request->subject_ids[$key],
            ]);
        }
        $dateSheetUnit = DateSheet::find($request->ids[0]);
        $dateSheet = $dateSheetUnit->exam->date_sheets()->orderBy('date', 'asc')->get();

        return $this->sendResponse(DateSheetResource::collection($dateSheet), []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DateSheet  $dateSheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(DateSheet $dateSheet)
    {
        $dateSheet->delete();

        return $this->sendResponse('deleted successfully', []);
    }

    public function deleteDateSheetSubject($id)
    {
        $subject = DateSheetSubject::find($id);
        $subject_name = Subject::find($subject->subject_id)->name;
        $message = $subject_name.' subject removed from date sheet successfully';
        $subject->delete();

        return $this->sendResponse($message, []);
    }

    public function addNoteInDateSheet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:date_sheets,id',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $date_sheet = DateSheet::find($request->id);

        $date_sheet->update(['note' => $request->note]);

        return $this->sendResponse($date_sheet, 'Note added in date sheet');
    }
}
