<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\SingleStudentChallanRequest;
use App\Http\Resources\FeeChallanDetailResource;
use App\Http\Resources\FeeChallanResource;
use App\Http\Resources\FeeChallanResourceCopy;
use App\Http\Resources\StudentResource;
use App\Models\Campus;
use App\Models\FeeChallan;
use App\Models\FeeChallanDetail;
use App\Models\Student;
use App\Repository\FeesChallanRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FeesChallanController extends BaseController
{
    public function __construct(FeesChallanRepository $feesChallanRepository)
    {
        $this->feesChallanRepository = $feesChallanRepository;
    }

    public function showAllChallans() // unpaid
    {
        // code...
        $challans = FeeChallan::where('status', 0)->latest()->get();
        $challans_for_total = FeeChallan::topChallan()->where('status', 0)->get();
        $challans->load('feeChallanDetails');
        $totalPaid = $challans_for_total->sum('paid');
        $totalPayable = $challans_for_total->sum('payable');
        $netPayable = $totalPayable - $totalPaid;
        $data = [
            'total payable' => $totalPayable,
            'total paid' => $totalPaid,
            'net payable' => $netPayable,
            'challans' => FeeChallanResource::collection($challans),

        ];

        return $this->sendResponse($data, [], 200);
    }

    public function showAllPaidChallans() // unpaid
    {
        // code...
        $challans = FeeChallan::where('status', 1)->latest()->get();
        $challans_for_total = FeeChallan::topChallan()->where('status', 1)->get();
        $challans->load('feeChallanDetails');
        $totalPaid = $challans_for_total->sum('paid');
        $totalPayable = $challans_for_total->sum('payable');
        $netPayable = $totalPayable - $totalPaid;
        $data = [
            'total payable' => $totalPayable,
            'total paid' => $totalPaid,
            'net payable' => $netPayable,
            'challans' => FeeChallanResource::collection($challans),

        ];

        return $this->sendResponse($data, [], 200);
    }

    public function campusChallan(Campus $campus, $status = 0)
    {
        if ($status > 1) {
            return $this->sendError('status must be 0 or 1 your provided status is ' . $status, [], 422);
        }

        return  $this->sendResponse($this->feesChallanRepository->campusChallan($campus, $status), []);
    }

    public function classChallan(Campus $campus, $class_id, $education_type, $status = 0)
    {
        if ($status > 1) {
            return $this->sendError('status must be 0 or 1 your provided status is ' . $status, [], 422);
        }

        return $this->sendResponse($this->feesChallanRepository->classChallan($campus, $class_id, $education_type, $status), []);
    }

    public function sectionChallan(Campus $campus, $class_id, $section_id, $status = 0)
    {
        if ($status > 1) {
            return $this->sendError('status must be 0 or 1 your provided status is ' . $status, [], 422);
        }

        return $this->sendResponse($this->feesChallanRepository->sectionChallan($campus, $class_id, $section_id, $status), []);
    }

    public function ChallansGL(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in([0, 1, 2])],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'student_class_id' => ['nullable', 'exists:student_classes,id'],
            'global_section_id' => ['nullable', 'exists:global_sections,id'],
            'start_issue_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'end_issue_date' => ['nullable', 'date', 'date_format:Y-m-d'],
        ]);
        $status = $request->status;
        $campus_id = $request->campus_id;
        $student_class_id = $request->student_class_id;
        $global_section_id = $request->global_section_id;
        $start_date = $request->start_issue_date;
        $end_date = $request->end_issue_date;
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $student_ids = Student::where(function ($query) use ($campus_id) {
            return $campus_id != null ? $query->where('campus_id', '=', $campus_id) : '';
        })
            ->where(function ($query) use ($student_class_id) {
                return $student_class_id != null ? $query->where('student_class_id', $student_class_id) : '';
            })
            ->where(function ($query) use ($global_section_id) {
                return $global_section_id != null ? $query->where('global_section_id', $global_section_id) : '';
            })->get()
            ->pluck('id')->toArray();
        // return $student_ids;
        $query = FeeChallan::whereIn('student_id', $student_ids)->where('status', $status)
            ->where(function ($query) use ($start_date) {
                return $start_date != null ? $query->where('issue_date', '>=', $start_date) : '';
            })
            ->where(function ($query) use ($end_date) {
                return $end_date != null ? $query->where('issue_date', '<=', $end_date) : '';
            })
            ->get();
        $query->load('student', 'bank_account', 'feeChallanDetails');

        return $this->sendResponse(FeeChallanResource::collection($query), [], 200);
    }

    public function getChallanByNo($challan_no)
    {
        $challan = FeeChallan::with('feeChallanDetails')->where('challan_no', $challan_no)->first();

        if (!$challan) {
            return $this->sendError('No such record found', [], 404);
        }

        return $this->sendResponse($this->feesChallanRepository->getChallanByNo($challan_no), []);
    }

    public function searchByChallanNo(Request $request)
    {
        $challan = FeeChallan::with('feeChallanDetails')->where('challan_no', $request->challan_no)->first();

        if (!$challan) {
            return $this->sendError('No such record found', [], 404);
        }

        return $this->sendResponse($this->feesChallanRepository->getChallanByNo($request->challan_no), []);
    }

    public function feeReceiving(FeeChallan $feeChallan, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'received_date' => ['required', 'date'],
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
            // "only_challan_amount"=> ['nullable','boolean'],
            // "postponed"=> ['nullable','boolean'],
            // "late_fine"=> ['nullable','integer'],
            // "re_admission_amount"=> ['nullable','integer'],

        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        if ($feeChallan->parent_id != null) {
            return $this->sendError('Sub challan can\'t be submitted');
        }
        $data = $this->feesChallanRepository->feeReceiving($feeChallan, $request);

        if ($data) {
            return $this->sendResponse($data, []);
        }

        return $this->sendError('internal server error', []);
    }

    public function StudentUnpaidChllans(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admission_id' => ['nullable:registration_id', 'exists:students,admission_id'],
            'registration_id' => ['nullable:admission_id', 'exists:students,registration_id'],

        ]);
        if (!$request->filled('admission_id') and !$request->filled('registration_id')) {
            return $this->sendError('adm or reg both can not be empty', [], 500);
        }
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $result = $this->feesChallanRepository->studentUnpaidChllans($request);

        if ($result) {
            return $this->sendResponse($result, 'paid all unpaid fees', 200);
        }

        return $this->sendError('internal server error', [], 500);
    }

    public function editChallanDetailFeesubmit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => ['required', 'exists:students,id'],
            'fee_challan_detail_id' => ['required', 'exists:fee_challan_details,id'],
            'amount' => ['required', 'min:0', 'integer'],
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $result = $this->feesChallanRepository->editChallanDetailFeesubmit($request);

        if ($result) {
            return $this->sendResponse($result, 'fee edited succfully');
        }

        return $this->sendError('internal server error', [], 500);
    }

    public function submitStudentUnpaidChllans(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => ['required', 'exists:students,id'],
            'received_date' => ['required', 'date'],
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
            'late_fine' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $result = $this->feesChallanRepository->submitStudentUnpaidChllans($request);

        if ($result) {
            return $this->sendResponse($result, 'student unpaid fees are collected');
        }

        return $this->sendError('internal server error', [], 500);
    }

    public function destroy(FeeChallan $feeChallan)
    {
        if (!$feeChallan or $feeChallan->parent_id) {
            return $this->sendError(['sub challan can not be deleted'], []);
        }

        return $this->sendResponse($this->feesChallanRepository->destroy($feeChallan), []);
    }

    public function feeRoleback(FeeChallan $feeChallan)
    {
        if ($feeChallan->status == 0) {
            return $this->sendError(['this challan is not paid yet'], []);
        }

        return $this->sendResponse($this->feesChallanRepository->feeRoleback($feeChallan), []);
    }

    public function ChallanEdit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fee_challan_id' => ['required', 'exists:fee_challans,id'],
            'due_date' => ['required', 'date'],
            'challan_detail_ids.*' => ['required', 'exists:fee_challan_details,id'],
            'challan_detail_amounts.*' => ['required', 'integer'],

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        DB::beginTransaction();
        try {
            $amount = 0;

            foreach ($request->challan_detail_ids as $key => $fee_challan_id) {
                $feeChallanDetial = FeeChallanDetail::find($fee_challan_id);
                $feeChallanDetial->update(['amount' => $request->challan_detail_amounts[$key]]);
                $amount = $amount + $request->challan_detail_amounts[$key];
            }

            $challan = FeeChallan::find($request->fee_challan_id);
            $challan->update(['due_date' => $request->due_date, 'payable' => $amount]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->sendError('internal server error', [], 422);
        }
        DB::commit();
        $challan->load('feeChallanDetails');

        return $this->sendResponse(new FeeChallanResource($challan), []);
    }

    public function getChallanSplit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fee_challan_id' => ['required', 'exists:fee_challans,id'],
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        $challan = FeeChallan::find($request->fee_challan_id);

        if ($challan->parent_id) {
            return $this->sendError('Cant proceed, its sub challan', []);
        }
        $challan = FeeChallan::find($request->fee_challan_id);
        if ($challan->status > 0) {
            return $this->sendError('Cant proceed, paid challan', []);
        }
        // return $challan;
        if ($challan->childs()->exists()) {
            $challan_details = FeeChallan::where([ // geting all fee_challan_detail for that student unpaid
                'status' => 0,
                'student_id' => $challan->student_id,

            ])->get()->pluck('feeChallanDetails')->flatten();

            FeeChallanDetail::whereIn('id', $challan_details->pluck('id'))->where('fees_type_id', 23)->delete();
            FeeChallan::where('status', 0)->where('id', '!=', $challan->id)->where('student_id', $challan->student_id)->delete();
            FeeChallanDetail::whereIn('id', $challan_details->pluck('id')->toArray())->update(['fee_challan_id' => $challan->id]);
            $sum_amount = FeeChallanDetail::whereIn('id', $challan_details->pluck('id')->toArray())->get()->sum('amount');
            $challan->update(['payable' => $sum_amount]);
        }
        $challan->load('feeChallanDetails');

        return $this->sendResponse(new FeeChallanResourceCopy($challan), []);
    }

    public function challanSplit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fee_challan_id' => ['required', 'exists:fee_challans,id'],
            'fee_challan_detail_ids.*' => ['required', 'exists:fee_challan_details,id'],
            'due_date' => ['nullable', 'date', 'date_format:Y-m-d'],
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        //// give latest challan no
        $challan_latest_id = FeeChallan::max('id');
        $challan_latest = FeeChallan::find($challan_latest_id);
        $old_ch_no = $challan_latest ? $challan_latest->challan_no : 1;
        $new_challan_no = $old_ch_no + 1;
        ////

        $old_challan = FeeChallan::find($request->fee_challan_id);
        $new_challan = FeeChallan::create([
            'challan_no' => $new_challan_no,
            'student_id' => $old_challan->student_id,
            'campus_id' => $old_challan->campus_id,
            'issue_date' => date('Y-m-d'),
            'due_date' => $request->due_date,
            'payable' => 0,
        ]);
        FeeChallanDetail::whereIn('id', $request->fee_challan_detail_ids)->update([
            'fee_challan_id' => $new_challan->id,
        ]);

        $new_challan->update([
            'payable' => $new_challan->feeChallanDetails->sum('amount'),
        ]);
        $old_challan->update([
            'payable' => $old_challan->feeChallanDetails->sum('amount'),
        ]);
        $new_challan->load('feeChallanDetails');

        return $this->sendResponse(new FeeChallanResourceCopy($new_challan), []);
    }



    public function runit()
    {
        $challans = FeeChallan::all();
        foreach ($challans as $key => $challan) {
            FeeChallanDetail::where('fee_challan_id', $challan->id)->update(['student_id' => $challan->student_id]);
        }

        return $this->sendResponse([], 'data filled', 200);
    }

    public function unpaidChallanCombine(Request $request)
    {
        // return "LLLLLLLLLLLLLLLLLL";

        $validator = Validator::make($request->all(), [
            'year_id' => 'required|exists:sessions,id',
            'campus_id' => ['required', 'required_with:student_class_id', 'exists:campuses,id'],
            'student_class_id' => ['required', 'exists:student_classes,id'],
            'education_type' => ['required', 'numeric'],
            'global_section_id' => ['required', 'exists:global_sections,id'],

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }



        $unpaid_challan_ids = Feechallan::where(['status' => 0, 'campus_id' => $request->campus_id])->pluck('id')->toArray();
        $student_ids = Feechallan::where(['status' => 0, 'campus_id' => $request->campus_id])->pluck('student_id')->unique()->toArray();
        $fee_challan_detail_id = FeeChallanDetail::whereIn('fee_challan_id', $unpaid_challan_ids)->where('campus_id', $request->campus_id)->pluck('id')->toArray();
        $std = Student::whereIn('id', $student_ids)->where([
            'session_id' => $request->year_id,
            'campus_id' => $request->campus_id,
            'student_class_id' => $request->student_class_id,
            'education_type' => $request->education_type,
            'global_section_id' => $request->global_section_id,
            'status' => 2,
        ])->get();

        $std->load('campus.printAccountNos');

        $std->load([
            'feeChallanDetails' => function ($query) use ($fee_challan_detail_id) {
                return $query->whereIn('id', $fee_challan_detail_id)->with('feeChallan');
            },
        ]);
        $data = [
            'total amount' => $std->pluck('feeChallans')->flatten()->sum('payable'),
            'students' => StudentResource::collection($std),
        ];

        return $this->sendResponse($data, 'student wise challan');
    }

    public function feeChallanMonthWise(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => 'required|exists:sessions,id',
            'campus_id' => ['nullable', 'required_with:student_class_id', 'exists:campuses,id'],
            'student_class_id' => ['nullable', 'required_with:global_section_id', 'exists:student_classes,id'],
            'education_type' => ['nullable', 'required_with:student_class_id', 'numeric'],
            'global_section_id' => ['nullable', 'exists:global_sections,id'],
            'fee_status' => ['required', Rule::in([0, 1, 2])],
            //-----------------------------------------------------------
            'fee_month' => [
                'required', 'date_format:Y-m-d',
                function ($student, $fee_month, $fail) {
                    if (substr($fee_month, -2) != '01') {
                        $fail('Oops! something wrong with fee month');
                    }
                },
            ],
            //-----------------------------------------------------------

        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        $student_class_id = $request->student_class_id;
        $education_type = $request->education_type;
        $global_section_id = $request->global_section_id;

        $challan_ids = FeeChallanDetail::where('fee_month', $request->fee_month)->get()->pluck('feeChallan')->pluck('id')->unique();
        $fee_challans = Feechallan::whereIn('id', $challan_ids)->where('status', $request->fee_status)->get();
        $student_ids = $fee_challans->pluck('student_id');
        if ($request->filled('campus_id')) {
            $student_ids = Student::whereIn('id', $student_ids)->where('campus_id', $request->campus_id)->where('session_id', $request->year_id)
                ->where(function ($query) use ($student_class_id) {
                    return  $student_class_id != null ? $query->where('student_class_id', $student_class_id) : '';
                })
                ->where(function ($query) use ($education_type) {
                    return  $education_type != null ? $query->where('education_type', $education_type) : '';
                })
                ->where(function ($query) use ($global_section_id) {
                    return  $global_section_id != null ? $query->where('global_section_id', $global_section_id) : '';
                })
                ->pluck('id');
        }

        $challans = $fee_challans->whereIn('student_id', $student_ids);
        $challans->load('student.studentClass', 'student.campus.printAccountNos', 'student.globalSection', 'feeChallanDetails.feeChallan');
        $data = [
            'total_amount' => $challans->sum('payable'),
            'fee_challans' => FeeChallanResourceCopy::collection($challans),
        ];

        return $this->sendResponse($data, 'challans with amount', 200);
    }

    public function searchStudentWiseChallan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_keyword' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $isAdm = null;
        $isName = null;
        if (preg_match('~[0-9]+~', $request->search_keyword)) {
            $isAdm = true;
        } else {
            $isName = true;
        }
        if ($isName) {
            $students = Student::with('campus.printAccountNos', 'studentClass', 'globalSection')->where('name', 'like', '%' . $request->search_keyword . '%')->get();
        }
        if ($isAdm) {
            $students = Student::with('campus.printAccountNos', 'campus', 'studentClass', 'globalSection')->where('admission_id', $request->search_keyword)->get();
        }
        $challans = FeeChallan::where('status', 0)->whereIn('student_id', $students->pluck('id')->unique())->get();
        $fee_challan_detail_id = FeeChallanDetail::whereIn('fee_challan_id', $challans->pluck('id')->unique())->pluck('id')->unique();

        $students->load([
            'feeChallanDetails' => function ($query) use ($fee_challan_detail_id) {
                return $query->whereIn('id', $fee_challan_detail_id)->with('feeChallan');
            },
        ]);
        $data = [
            'total amount' => $students->pluck('feeChallans')->flatten()->sum('payable'),
            'students' => StudentResource::collection($students),
        ];

        return $this->sendResponse($data, 'student wise challan');
    }

    public function challanDetailUnpaid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 401);
        }

        $fee_month = Carbon::today()->subMonth(5)->firstOfMonth();
        $details = FeeChallanDetail::with('feeChallan')->where(['student_id' => $request->student_id])->whereDate('fee_month', '>=', $fee_month)->get();
        $data = [
            'fee_challan_details' => FeeChallanDetailResource::collection($details),
        ];

        return $this->sendResponse($data, [], 200);
    }

    public function singleStudentFeeChallanMonthWise(SingleStudentChallanRequest $request)
    {
        $student = Student::find($request->student_id);

        $challan_ids = FeeChallanDetail::where('fee_month', $request->fee_month)->get()->pluck('feeChallan')->pluck('id')->unique();

        $fee_challans = Feechallan::whereIn('id', $challan_ids)
            ->where('status', $request->fee_status)
            ->where('student_id', $student->id)
            ->get();

        $fee_challans->load('student.studentClass', 'student.campus.printAccountNos', 'student.globalSection', 'feeChallanDetails.feeChallan');
        $data = [
            'total_amount' => $fee_challans->sum('payable'),
            'fee_challans' => FeeChallanResourceCopy::collection($fee_challans),
        ];

        return $this->sendResponse($data, 'challans with amount', 200);
    }
}
