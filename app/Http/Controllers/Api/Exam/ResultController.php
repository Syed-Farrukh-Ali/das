<?php

namespace App\Http\Controllers\Api\Exam;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\CampusResourceSimple;
use App\Http\Resources\DateSheet\DateSheetResource;
use App\Http\Resources\ExamResource;
use App\Http\Resources\FeeChallanResourceCopy;
use App\Http\Resources\GlobalSectionResource;
use App\Http\Resources\ResultResource;
use App\Http\Resources\StudentClassResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\StudentResourcePure;
use App\Http\Resources\SubjectResource;
use App\Models\Campus;
use App\Models\Exam;
use App\Models\GlobalSection;
use App\Models\Result;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ResultController extends BaseController
{
    public function __construct()
    {
        $this->middleware('school')->only(['resultGet', 'resultUpdate', 'studentResultGetAdm']);
        // $this->middleware('subscribed')->except('store');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function resultGet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => 'required|integer|exists:sessions,id',
            'exam_id' => 'required|exists:exams,id',
            'campus_id' => 'required|exists:campuses,id',
            'student_class_id' => 'nullable|exists:student_classes,id',
            'education_type' => 'required|numeric',
            'global_section_id' => 'nullable|exists:global_sections,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        // $student_ids = Student::where([
        //     'campus_id' => $request->campus_id,
        //     'student_class_id' => $request->student_class_id,
        //     'education_type' => $request->education_type,
        // ])->when($request->global_section_id, function ($query) use ($request) {
        //     return $query->where('global_section_id', $request->global_section_id);
        // })
        //     ->pluck('id')->toArray();

        $results = Result::where('exam_id', $request->exam_id)
            ->where('session_id', $request->year_id)
            ->where('campus_id', $request->campus_id)
            // ->where('student_class_id', $request->student_class_id)
            // ->where('global_section_id', $request->global_section_id)
            ->where('subject_id', $request->subject_id)
            ->when($request->global_section_id !== null, function ($query) use ($request) {
                return $query->where('global_section_id', $request->global_section_id);
            })
            // ->whereIn('student_id', $student_ids)
            ->when($request->student_class_id !== null, function ($query) use ($request) {
                return $query->where('student_class_id', $request->student_class_id);
            })
            ->whereHas('student', function ($subquery) use ($request) {
                $subquery->where('education_type', $request->education_type);
            })
            ->get();

        // return $this->sendResponse($results, []);

        $results->load('student', 'subject');

        $section = null;
        if ($request->global_section_id) {
            $section = new GlobalSectionResource(GlobalSection::find($request->global_section_id));
        }

        // $data = [
        //     'results' => ResultResource::collection($results),
        //     'exam' => new ExamResource(Exam::with('campus', 'exam_type', 'session')->find($request->exam_id)),
        //     'class' => new StudentClassResource(StudentClass::find($request->student_class_id)),
        //     'section' => $section,
        //     'subject' => new SubjectResource(Subject::find($request->subject_id)),
        // ];

        $data = [
            'results' => ResultResource::collection($results),
            'exam' => new ExamResource(Exam::with('campus', 'exam_type', 'session')->find($request->exam_id)),
            'section' => $section,
            'subject' => new SubjectResource(Subject::find($request->subject_id)),
        ];

        if ($request->student_class_id !== null) {
            $data['class'] = new StudentClassResource(StudentClass::find($request->student_class_id));
        }

        return $this->sendResponse($data, []);
    }

    public function resultStudentWise(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:exams,id',
            'campus_id' => 'required|exists:campuses,id',
            'student_class_id' => 'nullable|exists:student_classes,id',
            'education_type' => 'required|numeric',
            'global_section_id' => 'nullable|exists:global_sections,id',
            'include_annual_term' => 'required|boolean',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        if ($request->include_annual_term) {
            $exam_ids = Result::orderBy('sequence', 'ASC')->where([
                'campus_id' => $request->campus_id,
            ])
                ->when($request->global_section_id !== null, function ($query) use ($request) {
                    return $query->where('global_section_id', $request->global_section_id);
                })
                // ->whereIn('student_id', $student_ids)
                ->when($request->student_class_id !== null, function ($query) use ($request) {
                    return $query->where('student_class_id', $request->student_class_id);
                })
                ->pluck('exam_id')->unique()->toArray();
        }

        $campus = Campus::find($request->campus_id, ['id', 'name']);
        $student_class = StudentClass::find($request->student_class_id, ['id', 'name']);
        $global_section = GlobalSection::find($request->global_section_id, ['id', 'name']);
        $exam = Exam::with('exam_type', 'session')->find($request->exam_id);
        $results = Result::orderBy('sequence', 'ASC')->where([
            'exam_id' => $request->exam_id,
            'campus_id' => $request->campus_id,
        ])
            ->when($request->student_class_id !== null, function ($query) use ($request) {
                return $query->where('student_class_id', $request->student_class_id);
            })
            ->when($request->global_section_id !== null, function ($query) use ($request) {
                return $query->where('global_section_id', $request->global_section_id);
            })
            ->get(['student_id', 'status', 'full_marks', 'gain_marks', 'practical_marks', 'percentage', 'grade', 'subject_id']);
        $student_ids = $results->pluck('student_id')->unique()->toArray();

        $student_class_wise_ids = Student::whereIn('id', $student_ids)
            ->where('education_type', $request->education_type)
            ->pluck('id')
            ->toArray();

        $students_full_result = [];
        foreach ($student_class_wise_ids as $key => $student_id) {
            $student = Student::with('globalSection')->with('studentClass')->find($student_id, ['id', 'notification_id', 'name', 'father_name', 'admission_id', 'picture', 'global_section_id', 'student_class_id']);
            $student_result = $results->where('student_id', $student->id);
            $student_result->load('subject')->toArray();
            $subject_count = $student_result->count();
            if (!$subject_count) {
                continue;
            }

            if ($request->include_annual_term) {
                $student_result_overall = [];
                foreach ($exam_ids as $exam_id) {
                    $loop_exam_type_id = Exam::find($exam_id)->exam_type->id;
                    if ($exam->exam_type->id == $loop_exam_type_id || $loop_exam_type_id == 1) {
                        $std_result = Result::with('subject')->where('exam_id', $exam_id)->where('student_id', $student_id)->get();
                        $student_result_overall[] = [
                            'exam' => Exam::find($exam_id)->exam_type->name,
                            'full_marks' => $std_result->sum('full_marks'),
                            'gain_marks' => $std_result->sum('gain_marks'),
                            'practical_marks' => $std_result->sum('practical_marks'),
                            'percentage' => $std_result->sum('full_marks') ? round(((100 * $std_result->sum('gain_marks') + $std_result->sum('practical_marks')) / $std_result->sum('full_marks')), 2) . '%' : '',
                            'fail_in' => $std_result->where('percentage', '<', 40)->pluck('subject.slug')->toArray(),
                        ];
                    }
                }
            } else {
                $student_result_overall = [
                    'full_marks' => $student_result->sum('full_marks'),
                    'gain_marks' => $student_result->sum('gain_marks'),
                    'practical_marks' => $student_result->sum('practical_marks'),
                    'percentage' => $student_result->sum('full_marks') ? round(((100 * $student_result->sum('gain_marks') + $student_result->sum('practical_marks')) / $student_result->sum('full_marks')), 2) . '%' : '',
                    'fail_in' => $student_result->where('percentage', '<', 40)->pluck('subject.slug')->toArray(),
                ];
            }
            array_push($students_full_result, [
                'student' => new StudentResourcePure($student),
                'student_result' => $student_result->values()->all(),
                'student__result_overall' => $student_result_overall,
            ]);
        }
        $data = [
            'exam' => $exam,
            'campus' => $campus,
            'student_class' => $student_class,
            'global_section' => $global_section,
            'students_full_result' => $students_full_result,
        ];

        return $this->sendResponse($data, 'result is ready for print', 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function resultUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'result_id.*' => 'required|exists:results,id',
            'full_marks.*' => 'required|integer|min:0',
            'gain_marks.*' => 'required|integer|min:0|max:150',
            'practical_marks.*' => 'nullable|integer|max:150',
            'status.*' => 'nullable|string',
            'percentage.*' => 'required|numeric|max:100|min:0',
            'grade.*' => ['required', Rule::In(['A', 'B', 'C', 'D', 'E', 'F', 'F+', 'A+', 'B+'])],
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        foreach ($request->result_id as $key => $id) {
            Result::where('id', $id)->update([
                'full_marks' => $request->full_marks[$key],
                'gain_marks' => $request->gain_marks[$key],
                'status' => $request->status[$key],
                'practical_marks' => $request->practical_marks[$key],
                'percentage' => $request->percentage[$key],
                'grade' => $request->grade[$key],
            ]);
        }

        $results = Result::whereIn('id', $request->result_id)->get();

        return $this->sendResponse(ResultResource::collection($results), []);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function studentResultGet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'required|exists:students,id',
            'student_class_id' => 'required|integer|exists:student_classes,id',
            'year_id' => 'required|integer|exists:sessions,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $student = Student::find($request->student_id);
        $exam = Exam::find($request->exam_id);
        $results = $exam->results()->where('student_id', $request->student_id)
            ->where('session_id', $request->year_id)
            ->where('student_class_id', $request->student_class_id)
            ->get();
        $datesheets = $exam->date_sheets()->where('student_class_id', $student->student_class_id)->get();
        $results->load('subject', 'exam.session', 'exam.exam_type');
        $data = [
            'student' => $student,
            'exam' => new ExamResource($exam->load('session', 'exam_type')),
            'result' => ResultResource::collection($results),
            'datesheet' => DateSheetResource::collection($datesheets),
        ];

        return $this->sendResponse($data, []);
    }

    public function stdExamList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'year_id' => 'required|integer|exists:sessions,id',
            'student_class_id' => 'required|integer|exists:student_classes,id'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $std_exams = Result::where('student_id', $request->student_id)
            ->where('student_class_id', $request->student_class_id)
            ->where('session_id', $request->year_id)
            ->pluck('exam_id')
            ->unique()
            ->toArray();

        $exams = Exam::whereIn('id', $std_exams)->where('status', 1)->get()->load('session', 'exam_type');

        return $this->sendResponse(ExamResource::collection($exams), []);
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
     * @param  \App\Models\Result  $result
     * @return \Illuminate\Http\Response
     */
    public function show(Result $result)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Result  $result
     * @return \Illuminate\Http\Response
     */
    public function resultSequanceSeting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required',
            'campus_id' => 'required|exists:campuses,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'education_type' => 'required|numeric',
            'subject_ids.*'    => 'required|exists:subjects,id'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $student_ids = Student::where('campus_id', $request->campus_id)
            ->where('student_class_id', $request->student_class_id)
            ->where('education_type', $request->education_type)
            ->pluck('id')
            ->toArray();

        foreach ($request->subject_ids as $key => $subject_id) {
            Result::where('exam_id', $request->exam_id)
                ->where('subject_id', $subject_id)
                ->whereIn('student_id', $student_ids)
                ->update(['sequence' => $key + 1]);
        }

        return $this->sendResponse(true, 'sequenced the result of given class', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Result  $result
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Result $result)
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function studentResultGetAdm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:exams,id',
            'admission_id' => 'required|exists:students,admission_id',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $student = Student::where('admission_id', $request->admission_id)->get(['id', 'name', 'father_name', 'campus_id', 'admission_id', 'student_class_id', 'global_section_id', 'picture'])->first();

        $exam = Exam::find($request->exam_id);
        $results = $exam->results()->where('student_id', $student->id)->get();
        $results->load('subject', 'exam.session', 'exam.exam_type');

        $subject_count = $results->count();

        $student_result_overall = [
            'full_marks' => $results->sum('full_marks'),
            'gain_marks' => $results->sum('gain_marks') + $results->sum('practical_marks'),
            'practical_marks' => $results->sum('practical_marks'),
            'percentage' => $results->sum('full_marks') ? round(((100 * $results->sum('gain_marks') + $results->sum('practical_marks')) / $results->sum('full_marks')), 2) . '%' : '',
            'fail_in' => $results->where('percentage', '<', 40)->pluck('subject.slug')->toArray(),
        ];
        $student_result_detial = [
            'student' => new StudentResource($student),
            'student_result' => ResultResource::collection($results),
            'student__result_overall' => $student_result_overall,
        ];

        $data = [
            'exam' => new ExamResource($exam->load('session', 'exam_type')),
            'campus' => new CampusResourceSimple($exam->campus ?? $student->campus),
            'student_class' => $results->first()->student_class ?? $student->studentClass,
            'global_section' => $results->first()->global_section,
            'students_full_result' => $student_result_detial,
        ];

        return $this->sendResponse($data, []);
    }

    public function stdPaidFeeHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => 'required|integer|exists:sessions,id'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $student = auth()->user()->student;
        $fee_month = Carbon::today()->subMonth(11)->firstOfMonth();
        $voucher_ids = Voucher::where('session_id', $request->year_id)->pluck('id')->toArray();
        $challan_detail_of_past_6month = $student->feeChallans()->with('feeChallanDetails')->whereDate('issue_date', '>=', $fee_month)->whereIn('voucher_id', $voucher_ids)->get();
        $data = [
            'fee_challans' => FeeChallanResourceCopy::collection($challan_detail_of_past_6month),
        ];

        return $this->sendResponse($data, 'past 12 months paid unpaid challans', 200);
    }

    public function stdUnPaidFeeHistory()
    {
        $fee_month = Carbon::today()->subMonth(11)->firstOfMonth();
        $student = _user()->student;
        $challan_detail_of_past_6month = $student->feeChallans()->with('feeChallanDetails')
            ->whereDate('issue_date', '>=', $fee_month)
            ->where('status', 0)
            ->where('voucher_id', null)
            ->get();
        $data = [
            'fee_challans' => FeeChallanResourceCopy::collection($challan_detail_of_past_6month),
        ];

        return $this->sendResponse($data, 'past 12 months paid unpaid challans', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Result  $result
     * @return \Illuminate\Http\Response
     */
    public function destroy(Result $result)
    {
        //
    }
}
