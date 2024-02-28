<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\StudentResource;
use App\Models\FeeChallanDetail;
use App\Models\FeesType;
use App\Models\Student;
use App\Repository\FeesGeneratorRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FeesGeneratorController extends BaseController
{
    public function __construct(FeesGeneratorRepository $feesGeneratorRepository)
    {
        $this->feesGeneratorRepository = $feesGeneratorRepository;
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function CustomFeesGenerator(Request $request, Student $student)
    {
        if ($request->monthly_fee_status == 1) {
            $std_challans_ids = $student->feeChallans->pluck('id');

            $count = FeeChallanDetail::whereIn('fee_challan_id', $std_challans_ids)->whereIn('fee_month', $request->fee_month)->get()->count();

            if ($count > 0 and $student->status == 2) {
                return $this->sendError('monthly fees for given month is already generated', [], 422);
            }
        }

        $validator = Validator::make($request->all(), [
            'monthly_fee_status' => ['required', Rule::in([1, 0])],
            'fee_month.*' => [
                'nullable', 'date_format:Y-m-d',
                function ($student, $fee_month, $fail) {
                    if (substr($fee_month, -2) != '01') {
                        $fail('Oops! something wrong with fee month');
                    }
                },
            ],
            'due_date' => ['required', 'date', 'date_format:Y-m-d'],
            'issue_date' => ['required', 'date', 'date_format:Y-m-d'],
            'additional_fee_status' => ['required', Rule::in([1, 0])],
            'amount.*' => ['nullable', 'integer'],
            'fees_type_id.*' => ['nullable', 'integer', Rule::in(FeesType::all()->pluck('id')->toArray())],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $result = $this->feesGeneratorRepository->CustomFeesGenerator($request, $student);

        if ($result) {
            return  $this->sendResponse($result, [], 200);
        }

        return $this->sendError('internal server error', [], 500);
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function showStdChallans($id)
    {
        return $this->sendResponse($this->feesGeneratorRepository->showStdChallans($id), []);
    }

    public function showStdChallanHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        $data = $this->feesGeneratorRepository->showStdChallanHistory($request);

        if ($data) {
            return $this->sendResponse($data, [], 200);
        }

        return $this->sendError('no challan or server side error', [], 500);
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function allFeeStdList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campus_id' => ['required', 'exists:campuses,id'],
            'student_class_id' => ['nullable', 'exists:student_classes,id'],
            'education_type' => ['nullable', 'numeric'],
            'section_id' => ['nullable', 'exists:global_sections,id'],
            'year_id' => ['nullable', 'exists:sessions,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $students = $this->feesGeneratorRepository->allFeeStdList($request);

        if ($students) {
            return $this->sendResponse(StudentResource::collection($students), []);
        }

        return $this->sendError('internal server error', [], 500);
    }

    public function feeGenerateByStdList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id.*' => ['required', 'integer', 'exists:students,id'],
            'monthly_fee_status' => ['required'],
            'additional_fee_status' => ['required'],
            'due_date' => ['required', 'date', 'date_format:Y-m-d'],
            'issue_date' => ['required', 'date', 'date_format:Y-m-d'],
            'add_fine' => ['required', 'min:0', 'max:1', 'integer'],

            'fee_month.*' => [
                'nullable', 'date_format:Y-m-d',
            ],

            'fees_type_id.*' => ['nullable', 'integer'],
            'amount.*' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $result = $this->feesGeneratorRepository->feeGenerateByStdList($request);

        if ($result) {
            return $this->sendResponse($result, []);
        }

        return $this->sendError('internal server error', [], 500);
    }
    public function feeGenerateByStdListNew(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id.*' => ['integer', Rule::in(Student::all()->pluck('id')->toArray())],
            'monthly_fee_status' => ['required', Rule::in([1, 0])],
            'additional_fee_status' => ['required', Rule::in([1, 0])],
            'due_date' => ['required', 'date', 'date_format:Y-m-d'],
            'issue_date' => ['required', 'date', 'date_format:Y-m-d'],
            'add_fine' => ['required', 'min:0', 'max:1', 'integer'],

            'fee_month.*' => [
                'nullable', 'date_format:Y-m-d',
                function ($student, $fee_month, $fail) {
                    if (substr($fee_month, -2) != '01') {
                        $fail('Oops! something wrong with fee month');
                    }
                },
            ],

            'fees_type_id.*' => ['nullable', 'integer', Rule::in(FeesType::all()->pluck('id')->toArray())],
            'amount.*' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $result = $this->feesGeneratorRepository->feeGenerateByStdListNew($request);

        if ($result) {
            return $this->sendResponse($result, []);
        }

        return $this->sendError('internal server error', [], 500);
    }
}
