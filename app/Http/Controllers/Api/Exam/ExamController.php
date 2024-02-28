<?php

namespace App\Http\Controllers\Api\Exam;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\ExamRequest;
use App\Http\Resources\ExamResource;
use App\Models\DateSheetSubject;
use \Carbon\Carbon;

use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Result;
use App\Models\Student;
use App\Models\StudentSubject;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExamController extends BaseController
{
    public function __construct()
    {
        $this->middleware('school')->only(['store', 'destroy']);
        // $this->middleware('subscribed')->except('store');
    }

    public function index()
    {
        $exams = Exam::latest()->get();
        $exams->load('session', 'exam_type', 'student_classes', 'campus');

        return $this->sendResponse(ExamResource::collection($exams), []);
    }

    public function examForSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => 'required|exists:sessions,id',
            'campus_id' => 'nullable|exists:campuses,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        $campus_id = $request->campus_id;

        $exams = Exam::where('session_id', $request->year_id)
            ->when($campus_id, fn ($query) => $query->where('campus_id', $campus_id))
            ->latest()->get();
        $exams->load('session', 'exam_type', 'student_classes', 'campus');

        return $this->sendResponse(ExamResource::collection($exams), []);
    }

    public function updateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|integer|min:0|max:2',
            'date_sheet_status' => 'required|integer|min:0|max:2',
            'exam_id' => 'required|exists:exams,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        $exam = Exam::find($request->exam_id);
        $exam->update([
            'status' => $request->status,
            'date_sheet_status' => $request->date_sheet_status
        ]);

        return $this->sendResponse(new ExamResource($exam), []);
    }

    public function store(ExamRequest $request)
    {
        DB::beginTransaction();

        try {
            $exam = $this->createExam($request);

            $exam->student_classes()->sync($request->student_class_ids);

            $students = $this->getStudents($request);

            $resultData = $this->prepareResultData($students, $request, $exam);

            // New Addition in Coode for inserting results in Chunck
            foreach (array_chunk($resultData, 1000) as $result) {
                DB::table('results')->insert($result);
            }

            // Result::insert($resultData);

            $exam->load('student_classes', 'exam_type', 'session', 'results');

            DB::commit();

            return $this->sendResponse(new ExamResource($exam), '');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function addExamNames()
    {
        $exams = Exam::all();

        foreach ($exams as $exam) {

            $examName = $exam->exam_type()->pluck('name')->first();

            if ($examName) {
                $exam->update([
                    'exam_name' => $examName,
                ]);
            }
        }

        return response()->json(['message' => 'Exam names added successfully'], 200);
    }

    public function updateExamName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|integer|exists:exams,id',
            'exam_name' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $exam = Exam::where('id', $request->exam_id);

        $examName = $request->exam_name;

        if ($examName) {
            $exam->update([
                'exam_name' => $examName,
            ]);
        }

        return $this->sendResponse([], 'Exam Name Updated Successfully', 200);
    }

    public function updateExamClass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|integer|exists:exams,id',
            'student_class_ids.*' => 'required|integer|exists:student_classes,id',
            'education_type' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        DB::beginTransaction();
        try {
            $exam = Exam::find($request->exam_id);
            $exam->student_classes()->syncWithoutDetaching($request->student_class_ids);

            $students = Student::with(['subjects', 'results' => function ($query) use ($exam) {
                $query->where('exam_id', $exam->id);
            }])
                ->whereIn('student_class_id', $request->student_class_ids)
                ->where('campus_id', $exam->campus_id)
                ->where('education_type', $request->education_type)
                ->where(['session_id' => $exam->session_id, 'status' => 2])
                ->get();

            $delete_result_ids[] = null;

            foreach ($students as $student) {
                $subject_ids = $student->subjects->pluck('id')->toArray();
                $results = $student->results->where('exam_id', $exam->id);

                $result_subject_ids = $results->pluck('subject_id')->toArray();

                if ($subject_ids != $result_subject_ids) {
                    $missing_in_results = array_diff($subject_ids, $result_subject_ids);
                    $addition_in_results = array_diff($result_subject_ids, $subject_ids);


                    if ($missing_in_results) {

                        foreach ($missing_in_results as $subject_id) {

                            $student->results()->firstOrCreate([
                                'exam_id' => $exam->id,
                                'subject_id' => $subject_id,
                            ], [
                                'exam_type_id' => $exam->exam_type_id,
                                'session_id' => $exam->session_id,
                                'campus_id' => $student->campus_id,
                                'student_class_id' => $student->student_class_id,
                                'global_section_id' => $student->global_section_id,
                            ]);
                        }
                    }

                    if ($addition_in_results) {
                        $new_result_ids = $results->whereIn('subject_id', $addition_in_results)->pluck('id');

                        $delete_result_ids = array_merge($delete_result_ids, $new_result_ids->toArray());
                    }
                }
            }
            $delete_result_ids = array_filter($delete_result_ids); // Remove any null values if present

            if ($delete_result_ids)
                Result::whereIn('id', $delete_result_ids)->delete();

            // return $this->sendResponse(new ExamResource($exam), 'student added successfully', 200);
            DB::commit();
            return $this->sendResponse([], 'Classes Updated Successfully', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->sendError('internal server error', [], 500);
        }
    }

    public function addExamStudent(Request $request, Exam $exam)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|integer|exists:exams,id',
            'student_id' => 'required|integer|exists:students,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
            // code...
        }
        DB::beginTransaction();
        try {
            $student = Student::find($request->student_id);
            $exam = Exam::find($request->exam_id);

            $exam->student_classes()->syncWithoutDetaching([$student->student_class_id]);

            $delete_result_ids[] = null;

            $subject_ids = $student->subjects->pluck('id')->toArray();
            $results = $student->results->where('exam_id', $exam->id);

            $result_subject_ids = $results->pluck('subject_id')->toArray();

            if ($subject_ids != $result_subject_ids) {
                $missing_in_results = array_diff($subject_ids, $result_subject_ids);
                $addition_in_results = array_diff($result_subject_ids, $subject_ids);


                if ($missing_in_results) {

                    foreach ($missing_in_results as $subject_id) {

                        $student->results()->firstOrCreate([
                            'exam_id' => $exam->id,
                            'subject_id' => $subject_id,
                        ], [
                            'exam_type_id' => $exam->exam_type_id,
                            'session_id' => $exam->session_id,
                            'campus_id' => $student->campus_id,
                            'student_class_id' => $student->student_class_id,
                            'global_section_id' => $student->global_section_id,
                        ]);
                    }
                }

                if ($addition_in_results) {
                    $new_result_ids = $results->whereIn('subject_id', $addition_in_results)->pluck('id');

                    $delete_result_ids = array_merge($delete_result_ids, $new_result_ids->toArray());
                }
            }

            $delete_result_ids = array_filter($delete_result_ids); // Remove any null values if present

            if ($delete_result_ids)
                Result::whereIn('id', $delete_result_ids)->delete();

            DB::commit();
            return $this->sendResponse([], 'Student Updated Successfully', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->sendError('internal server error', [], 500);
        }
    }

    public function removeExamClasses(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|integer|exists:exams,id',
            'student_class_ids.*' => 'required|integer|exists:student_classes,id',
            'education_type' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $exam = Exam::Find($request->exam_id);

        if (!$exam) {
            return $this->sendError([], 'Exam not found', 404);
        }

        DB::beginTransaction();
        try {
            $class_ids = $request->student_class_ids;

            $exam->student_classes()->detach($class_ids);
            $exam->results()->whereIn('student_class_id', $class_ids)->forceDelete();
            $date_sheet_id = $exam->date_sheets()->whereIn('student_class_id', $class_ids)->pluck('id')->first();
            DateSheetSubject::where('date_sheet_id', $date_sheet_id)->forceDelete();
            $exam->date_sheets()->whereIn('student_class_id', $class_ids)->forceDelete();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->sendError('internal server error', [], 500);
        }

        DB::commit();
        return $this->sendResponse([], 'Classes Removed Successfully', 200);
    }

    public function removeExamStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|integer|exists:exams,id',
            'student_id' => 'required|integer|exists:students,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $exam = Exam::Find($request->exam_id);

        if (!$exam) {
            return $this->sendError([], 'Exam not found', 404);
        }

        DB::beginTransaction();
        try {
            $student_id = $request->student_id;
            $exam->results()->where('student_id', $student_id)->forceDelete();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->sendError('internal server error', [], 500);
        }

        DB::commit();
        return $this->sendResponse([], 'Student Removed Successfully', 200);
    }

    public function destroy($id)
    {
        $exam = Exam::Find($id);

        if (!$exam) {
            return $this->sendError('Exam not found', 404);
        }

        $exam->delete();
        $exam->student_classes()->detach();
        $exam->results()->forceDelete();

        $exam->date_sheets->each(function ($dateSheet) {
            $dateSheet->dateSheetSubjects()->forceDelete();
        });

        $exam->date_sheets()->forceDelete();

        return $this->sendResponse([], 'Exam Deleted Successfully', 200);
    }

    protected function createExam($request)
    {
        $examType = ExamType::Find($request->exam_type_id);

        return Exam::create([
            'exam_type_id' => $request->exam_type_id,
            'session_id'   => $request->year_id,
            'campus_id'    => $request->campus_id ?? null,
            'exam_name' => $examType->name,
        ]);
    }

    protected function getStudents($request)
    {
        return Student::whereIn('student_class_id', $request->student_class_ids)
            ->where('campus_id', $request->campus_id)
            ->where(['session_id' => $request->year_id, 'status' => 2])
            ->with('subjects')
            ->get();
    }

    protected function prepareResultData($students, $request, $exam)
    {
        $resultData = [];
        $currentDateTime = now();

        foreach ($students as $student) {
            foreach ($student->subjects as $subject) {
                $resultData[] = [
                    'student_id'         => $student->id,
                    'exam_id'            => $exam->id,
                    'subject_id'         => $subject->id,
                    'exam_type_id'       => $request->exam_type_id,
                    'session_id'         => $request->year_id,
                    'campus_id'          => $student->campus_id,
                    'student_class_id'   => $student->student_class_id,
                    'global_section_id'  => $student->global_section_id,
                    'created_at'         => $currentDateTime,
                    'updated_at'         => $currentDateTime,
                ];
            }
        }

        return $resultData;
    }
}
