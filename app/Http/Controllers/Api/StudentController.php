<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StudentNotificationIdsRequest;
use App\Http\Resources\FeeChallanResourceCopy;
use App\Http\Resources\StudentResource;
use App\Http\Resources\StudentResourcePure;
use App\Models\Campus;
use App\Models\Concession;
use App\Models\FeeChallan;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\StudentLiableFee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Repository\StudentRepository;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StudentController extends BaseController
{
    public function __construct(StudentRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse($this->studentRepository->index(), []);
    }

    public function campusStudents($campus_id)
    {
        return $this->sendResponse($this->studentRepository->campusStudents($campus_id), []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validateStudentStore($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $response = $this->studentRepository->storeWithFee($request);

        if ($response) {
            return $this->sendResponse($response, []);
        }
        return $this->sendError('internal server error', [], 500);
    }

    private function validateStudentStore(Request $request)
    {
        $fee_month = $request->fee_month;
        return Validator::make($request->all(), [
            'year_id' => 'required|exists:sessions,id',
            'campus_id' => 'required|exists:campuses,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'education_type' => 'required|integer|min:1|max:2',
            'course_id' => 'nullable|exists:courses,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'concession_id' => 'nullable|exists:concessions,id',
            'hostel_id' => 'nullable|exists:hostels,id',
            'global_section_id' => 'nullable|exists:global_sections,id',
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'employee_id' => 'nullable|exists:employees,id',
            'cnic_no' => 'nullable|string|max:20',
            'father_cnic' => 'nullable|string|max:20',
            'dob' => 'nullable|date|date_format:Y-m-d',
            'religion' => 'nullable|string|max:255',
            'gender' => 'required|string|max:255',
            'mobile_no' => 'required|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:255',
            'previous_school' => 'nullable|string|max:255',
            'class_left' => 'nullable|string|max:255',
            'leaving_date' => 'nullable|date|date_format:Y-m-d',
            'shift' => 'nullable|string|max:255',
            'status' => 'nullable|integer|max:15|min:1',
            'fee_month.*' => [
                'nullable',
                function ($attribute, $fee_month, $fail) {
                    if (substr($fee_month, -2) != '01') {
                        $fail('Oops! something wrong with fee month, required format is yyyy-mm-01');
                    }
                },
            ],

            'fee_type_id.*' => 'required|exists:fees_types,id',
            'fee_amount.*' => 'required|integer|max:30000',
            'concession_amount.*' => 'required|integer|max:30000',
            'fee_after_concession.*' => 'required|integer|max:30000',

            'admission_fee' => 'nullable|integer|max:30000|min:0',
            'prospectus_fee' => 'nullable|integer|max:30000|min:0',
            'annual_fund_fee' => 'nullable|integer|max:30000|min:0',
            'hostel_admission_fee' => 'nullable|integer|max:30000|min:0',

        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Student $student)
    {
        return $this->sendResponse($this->studentRepository->show($student), []);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function getByAdmissionId($adm_id)
    {
        return $this->sendResponse($this->studentRepository->getByAdmissionId($adm_id), []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Student $student)
    {
        return $this->sendError('student can not be deleted, you can struck off', [], 422);

        return $this->sendResponse($this->studentRepository->destroy($student), []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'status' => ['required', Rule::in([1, 2, 3, 4, 5, 6])],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->studentRepository->changeStatus($request), 'status changed', 200);
    }

    public function admAfterRegister(Request $request, $student_id)
    {
        $student = Student::find($student_id);

        $stdLiableFees = $student->studentLiableFees;
        $date = date('Y-m-d');

        $feestructures = FeeStructure::where([
            'campus_id' => $student->campus_id,
            'student_class_id' => $student->student_class_id,
            'session_id' => $student->session_id,
        ])->get();

        $old = FeeChallan::max('challan_no') ?? 1;
        $new_challan_no = $old + 1;

        DB::beginTransaction();
        try {
            $status = $student->status;
            $student->update(['status' => 3]);
            //voucher is creating then its detail will be created
            $feeChallan = $student->FeeChallans()->create([
                'campus_id' => $student->campus_id,
                'challan_no' => $new_challan_no,
                'payable' => null, //first fee chalan detail wil be created & then sum of amount wil be here
                'due_date' => $request->due_date ?? $date,
                'issue_date' => $date,
            ]);

            if ($status == 4) {
                $feeChallan->feeChallanDetails()->create([
                    'student_id' => $student->id,
                    'amount' => $feestructures->where('fee_type_id', 1)->first()->amount ?? 3300,
                    'fee_month' => $request->fee_month ?? substr($date, 0, 8) . '01',
                    'fee_name' => 'RE-ADMISSION FEE',
                    'campus_id' => $student->campus_id,
                    'fees_type_id' => 9,
                ]);
            } else {
                // monthly defined fees is creating,
                foreach ($stdLiableFees as $key => $stdLiableFee) {
                    $feeChallan->feeChallanDetails()->create([
                        'student_id' => $student->id,
                        'amount' => $stdLiableFee->amount,
                        'fee_month' => $request->fee_month ?? substr($date, 0, 8) . '01',
                        'fee_name' => $stdLiableFee->feesType->name,
                        'campus_id' => $student->campus_id,
                        'fees_type_id' => $stdLiableFee->fees_type_id,
                    ]);
                } // prospectus fees is defining
                // $feestructure = FeeStructure::where([
                //     'campus_id' => $student->campus_id,
                //     'student_class_id' => $student->student_class_id,
                //     'session_id'    => $student->session_id,
                //     'fee_type_id' => 1,
                // ])->first();

                $feeChallan->feeChallanDetails()->create([
                    'student_id' => $student->id,
                    'amount' => $feestructures->where('fee_type_id', 1)->first()->amount ?? 3300,
                    'fee_month' => $request->fee_month ?? substr($date, 0, 8) . '01',
                    'fee_name' => 'PROSPECTUS',
                    'campus_id' => $student->campus_id,
                    'fees_type_id' => 1,
                ]);

                $feeChallan->feeChallanDetails()->create([
                    'student_id' => $student->id,
                    'amount' => $feestructures->where('fee_type_id', 3)->first()->amount ?? 3300,
                    'fee_month' => $request->fee_month ?? substr($date, 0, 8) . '01',
                    'fee_name' => 'ADMISSION FEE',
                    'campus_id' => $student->campus_id,
                    'fees_type_id' => 3,
                ]);

                $feeChallan->feeChallanDetails()->create([
                    'student_id' => $student->id,
                    'amount' => $feestructures->where('fee_type_id', 5)->first()->amount ?? 2500,
                    'fee_month' => $request->fee_month ?? substr($date, 0, 8) . '01',
                    'fee_name' => 'ANNUAL FUND',
                    'campus_id' => $student->campus_id,
                    'fees_type_id' => 5,
                ]);
                if ($student->hostel_id) {
                    $feeChallan->feeChallanDetails()->create([
                        'student_id' => $student->id,
                        'amount' => $feestructures->where('fee_type_id', 6)->first()->amount ?? 3300,
                        'fee_month' => $request->fee_month ?? substr($date, 0, 8) . '01',
                        'fee_name' => 'HOSTEL ADMISSION FEE',
                        'campus_id' => $student->campus_id,
                        'fees_type_id' => 6,
                    ]);
                }
            }

            $feeChallan->update([
                'payable' => $feeChallan->feeChallanDetails()->sum('amount'),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);

            return $this->sendError('server side error', [$th], 500);
        }

        DB::commit();

        return $this->sendResponse([], 'challan created successfully', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Student $student)
    {
        $validator = $this->validateStudent($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->studentRepository->update($request, $student), []);
    }

    private function validateStudent(Request $request)
    {
        return Validator::make($request->all(), [
            'year_id' => 'required|exists:sessions,id',
            'campus_id' => 'required|exists:campuses,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'course_id' => 'nullable|exists:courses,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'concession_id' => 'nullable|exists:concessions,id',
            'hostel_id' => 'nullable|exists:hostels,id',
            'global_section_id' => 'nullable|exists:global_sections,id',
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'employee_id' => 'nullable|exists:employees,id',
            'cnic_no' => 'nullable|string|max:20',
            'father_cnic' => 'nullable|string|max:20',
            'dob' => 'nullable|date|date_format:Y-m-d',
            'religion' => 'nullable|string|max:255',
            'gender' => 'required|string|max:255',
            'mobile_no' => 'required|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:255',
            'previous_school' => 'nullable|string|max:255',
            'class_left' => 'nullable|string|max:255',
            'leaving_date' => 'nullable|date|date_format:Y-m-d',
            'shift' => 'nullable|string|max:255',
            'status' => 'nullable|integer|max:15|min:1',
            'struck_off_date' => 'nullable|date',
            'Joining_date' => 'nullable|date'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function changeConcession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'concession_id' => ['required', Rule::in(Concession::all()->pluck('id')->toArray())],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $response = $this->studentRepository->changeConcession($request);
        if ($response) {
            return $this->sendResponse('concession changed !', []);
        }

        return $this->sendError(['internal server error'], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function studentFilterList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => 'required|exists:sessions,id',
            'campus_id' => 'nullable|integer|exists:campuses,id',
            'student_class_id' => 'nullable|integer|exists:student_classes,id',
            'education_type' => 'nullable|integer',
            'global_section_id' => 'nullable|integer|exists:global_sections,id',
            'year_id' => 'nullable|integer|exists:sessions,id',
            'status' => ['required', Rule::in([1, 2, 3, 4, 5])],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->studentRepository->studentFilterList($request), []);
    }

    public function allRegisteredOrAdmitted(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campus_id' => 'required|integer|exists:campuses,id',
            'student_class_id' => 'nullable|integer|exists:student_classes,id',
            'education_type' => 'nullable|integer',
            'global_section_id' => 'nullable|integer|exists:global_sections,id',
            'year_id' => 'nullable|integer|exists:sessions,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->studentRepository->allRegisteredOrAdmitted($request), []);
    }

    public function stdJumpList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campus_id' => 'required|integer|exists:campuses,id',
            'student_class_id' => 'nullable|integer|exists:student_classes,id',
            'education_type' => 'nullable|integer',
            'global_section_id' => 'nullable|integer|exists:global_sections,id',
            'year_id' => ['required', 'integer', 'exists:sessions,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $students = Student::where([
            'campus_id' => $request->campus_id,
            'student_class_id' => $request->student_class_id,
            'education_type' => $request->education_type,
            'global_section_id' => $request->global_section_id,
            'session_id' => $request->year_id,
        ])
            ->where('status', '!=', 4)
            ->latest()->get();

        return $this->sendResponse(StudentResource::collection($students), [], 200);
    }

    public function UpdateLiableFees(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $student_id = $request->student_id;
        $student = Student::where('id', $student_id)->first();

        $old_fees = StudentLiableFee::where('student_id', $student_id)->first();


        $classID = $student->student_class_id;

        if ($student->education_type == 2) {
            $classID = 1;
        }


        $old_session_fees = FeeStructure::where('campus_id', $student->campus_id)
            ->where('student_class_id', $classID)
            ->where('session_id', $student->session_id)
            ->where('fee_type_id', 4)
            ->value('amount');


        $new_fees = FeeStructure::where('campus_id', $student->campus_id)
            ->where('student_class_id', $classID)
            ->where('session_id', $student->session_id)
            ->where('fee_type_id', 4)
            ->value('amount');

        if (!$old_fees)
            return $this->sendError("Liable Fees Not Defined", [], 422);

        if (!$new_fees) {
            return $this->sendError("New Session Fees Not Defined", [], 422);
        }

        $difference = $new_fees - $old_fees->amount;

        $liable_fees = $student->studentLiableFees()->where('fees_type_id', 4)->first();

        DB::beginTransaction();
        try {
            if ($liable_fees) {
                if ($student->concession_id) {

                    $concession = Concession::where('id', $student->concession_id)->first();

                    $percentage = $concession->amount === null ? $concession->percentage : null;

                    // If 'percentage' is null, retrieve 'amount'
                    $cons_amount = $concession->percentage === null ? $concession->amount : null;

                    if ($percentage) {
                        $fee = $new_fees * (1 - ($percentage / 100));
                        $cons_amount = $new_fees - $fee;

                        $liable_fees->update(['amount' => $fee, 'concession_amount' => $cons_amount]);
                    } else {
                        $cons_amount =  $old_session_fees - $old_fees->amount;

                        if ($cons_amount < 0)
                            return $this->sendError("Concession Amount Not Correct", [], 422);

                        if ($old_fees->amount == 0) {
                            $fee = 0;
                            $cons_amount = $new_fees;
                        } else {
                            $fee = $new_fees - $cons_amount;
                        }


                        $liable_fees->update(['amount' => $fee, 'concession_amount' => $cons_amount]);
                    }
                } else {
                    $liable_fees->update(['amount' => $new_fees]);
                }
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 500);
        }
        DB::commit();

        return $this->sendResponse("Fees Successfully Updated", [], 200);
    }

    public function stdJump(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campus_id' => 'required|integer|exists:campuses,id',
            'student_class_id' => 'nullable|integer|exists:student_classes,id',
            'global_section_id' => 'nullable|integer|exists:global_sections,id',
            'year_id' => ['required', 'integer', 'exists:sessions,id'],
            'education_type' => 'nullable|integer',
            'student_ids.*' => ['required', 'integer', 'exists:students,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $students = Student::whereIn('id', $request->student_ids)->get();

        DB::beginTransaction();
        try {
            foreach ($students as $key => $student) {

                //For Hifz
                $classID = $student->student_class_id;

                if ($student->education_type == 2) {
                    $classID = 1;
                }

                $old_session_fees = FeeStructure::where('campus_id', $student->campus_id)
                    ->where('student_class_id', $classID)
                    ->where('session_id', $student->session_id)
                    ->where('fee_type_id', 4)
                    ->value('amount');


                $student->update([
                    'campus_id' => $request->campus_id,
                    'student_class_id' => $request->student_class_id,
                    'global_section_id' => $request->global_section_id,
                    'session_id' => $request->year_id,
                    'education_type' => $request->education_type,
                ]);

                $old_fees = StudentLiableFee::where('student_id', $student->id)->first();

                if (!$old_fees)
                    continue;

                //For Hifz
                $classID = $request->student_class_id;

                if ($request->education_type == 2) {
                    $classID = 1;
                }

                $new_fees = FeeStructure::where('campus_id', $request->campus_id)
                    ->where('student_class_id', $classID)
                    ->where('session_id', $request->year_id)
                    ->where('fee_type_id', 4)
                    ->value('amount');

                if (!$new_fees) {
                    DB::rollBack();
                    return $this->sendError("New Session Fees Not Defined", [], 422);
                }

                $difference = $new_fees - $old_fees->amount;

                $liable_fees = $student->studentLiableFees()->where('fees_type_id', 4)->first();


                if ($difference > 0 && $liable_fees) {
                    if ($student->concession_id) {

                        $concession = Concession::where('id', $student->concession_id)->first();

                        $percentage = $concession->amount === null ? $concession->percentage : null;

                        // If 'percentage' is null, retrieve 'amount'
                        $cons_amount = $concession->percentage === null ? $concession->amount : null;

                        if ($percentage) {
                            $fee = $new_fees * (1 - ($percentage / 100));
                            $cons_amount = $new_fees - $fee;

                            $liable_fees->update(['amount' => $fee, 'concession_amount' => $cons_amount]);
                        } else {
                            // tvoxel code

                            $cons_amount =  $old_session_fees - $old_fees->amount;

                            if ($cons_amount <= 0)
                                continue;

                            if ($old_fees->amount == 0) {
                                $fee = 0;
                                $cons_amount = $new_fees;
                            } else {
                                $fee = $new_fees - $cons_amount;
                            }


                            $liable_fees->update(['amount' => $fee, 'concession_amount' => $cons_amount]);
                        }
                    } else {
                        $liable_fees->update(['amount' => $new_fees]);
                    }
                }
            }
        } catch (\Throwable $e) {
            //throw $th;
            DB::rollBack();
            // dd($e);
            return $this->sendError($e->getMessage(), [], 500);
        }
        DB::commit();

        return $this->sendResponse(StudentResource::collection($students->load('studentLiableFees')), [], 200);
    }
    public function stdPassOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_ids.*' => ['required', 'integer', 'exists:students,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $students = Student::whereIn('id', $request->student_ids)->get();
        DB::beginTransaction();
        try {
            foreach ($students as $key => $student) {
                $student->update([
                    'status' => 5,
                ]);
            }
        } catch (\Throwable $e) {
            //throw $th;
            DB::rollBack();
            // dd($e);
            return $this->sendError('Internal server error', [], 500);
        }
        DB::commit();

        return $this->sendResponse(StudentResource::collection($students), [], 200);
    }

    public function stdStructOff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        Student::find($request->student_id)->update(['status' => 4, 'struck_off_date' => $request->date]);

        return $this->sendResponse([], 'student struck off successfully', 200);
    }

    public function studentSignup(Request $request)
    {
        $url = Config::get('app.url');
        $parts = explode('-', $url);
        $test = $parts[0];
        $parts = explode('//', $test);
        $mailName = $parts[1];

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'password' => 'required|string|min:6',
            // 'status'     =>  ['required', Rule::in([1, 2, 3])],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $student = Student::find($request->student_id);
        DB::beginTransaction();
        try {
            $user = User::create([
                'first_name' => $student->name,
                'last_name' => '-',
                'campus_id' => $student->campus_id,
                'email' => Str::lower($student->admission_id) . '@' . $mailName . '.com',
                'password' => Hash::make($request->password),
            ]);
            $user->assignRole('Student');

            $student->update(['user_id' => $user->id]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->sendError($th->getMessage(), [], 500);
        }
        DB::commit();

        return $this->sendResponse('student signup successfully', [], 200);
    }

    public function studentAuthUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'password' => 'required|string|min:6',
            // 'status'     =>  ['required', Rule::in([1, 2, 3])],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $student = Student::find($request->student_id);
        DB::beginTransaction();
        try {
            //code...
            $student->user()->update([
                'password' => Hash::make($request->password),
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            dd($th);

            return $this->sendError('server side error', [], 500);
        }
        DB::commit();

        return $this->sendResponse('student password updated successfully', [], 200);
    }

    public function searchStudentByReg(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_id' => 'required|integer|exists:students,registration_id',

        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $students = Student::with('session', 'campus', 'studentClass', 'globalSection')->where('registration_id', $request->registration_id)->get();

        return $this->sendResponse(StudentResource::collection($students), 'searched student', 200);
    }

    public function searchStudentName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_name' => 'required|string',

        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $ids = Student::where('name', 'like', '%' . $request->student_name . '%')->get()->pluck('id');
        $fee_challans = FeeChallan::with('student', 'feeChallanDetails')->whereIn('student_id', $ids)->where('status', '<', 2)->get();
        $data = [
            'challans' => FeeChallanResourceCopy::collection($fee_challans),
        ];

        return $this->sendResponse($data, 'challans that machted the name', 200);
    }

    public function addNotificationIdToStudents()
    {
        $campus_map = Campus::pluck('code', 'id')->all();

        foreach (Student::cursor() as $student) {
            $campus_code = $campus_map[$student->campus_id] ?? Str::random(3);

            $time_stamp = Carbon::now()->format('d-m-Y-H-i-s');

            $notification_id = $campus_code . '-' . Str::random(26) . '-' . $time_stamp;

            $student->update(['notification_id' => $notification_id]);
        }

        return $this->sendResponse([], "Students notification ids updated successfully.");
    }
    public function searchNameId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_keyword' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $campus_id = _campusId();

        if ($campus_id) {
            $student = Student::with('campus', 'studentClass', 'globalSection', 'studentLiableFees')
                ->where('campus_id', $campus_id)
                ->where('name', 'like', '%' . $request->search_keyword . '%')
                ->orWhere('admission_id', $request->search_keyword)
                ->where('status', 2)
                ->get();
        } else {
            $student = Student::with('campus', 'studentClass', 'globalSection', 'studentLiableFees')
                ->where('name', 'like', '%' . $request->search_keyword . '%')
                ->orWhere('admission_id', $request->search_keyword)
                ->where('status', 2)
                ->get();
        }

        return $this->sendResponse(StudentResourcePure::collection($student), [], 200);
    }

    public function regIdNameAdmId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_keyword' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $campus_id = _campusId();

        if ($campus_id) {
            if (preg_match('~[A-z]+~', $request->search_keyword)) {
                $student = Student::with('campus', 'studentClass', 'globalSection', 'studentLiableFees')
                    ->where('campus_id', $campus_id)
                    ->where('name', 'like', '%' . $request->search_keyword . '%')
                    ->where('status', '!=', 2)
                    ->get();
            } else {
                $student = Student::with('campus', 'studentClass', 'globalSection', 'studentLiableFees')
                    ->where('campus_id', $campus_id)
                    ->where('registration_id', $request->search_keyword)
                    ->orWhere('admission_id', $request->search_keyword)
                    ->where('status', '!=', 2)
                    ->get();
            }
        } else {
            if (preg_match('~[A-z]+~', $request->search_keyword)) {
                $student = Student::with('campus', 'studentClass', 'globalSection', 'studentLiableFees')
                    ->where('name', 'like', '%' . $request->search_keyword . '%')
                    ->where('status', '!=', 2)
                    ->get();
            } else {
                $student = Student::with('campus', 'studentClass', 'globalSection', 'studentLiableFees')
                    ->where('registration_id', $request->search_keyword)
                    ->orWhere('admission_id', $request->search_keyword)
                    ->where('status', '!=', 2)
                    ->get();
            }
        }


        return $this->sendResponse(StudentResourcePure::collection($student), [], 200);
    }

    public function stdPictureUploading(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id.*' => 'required|string|exists:students,id',
            'student_img.*' => 'required|file',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        foreach ($request->student_id as $key => $student_id) {
            $student = Student::find($request->student_id[$key]);
            if ($request->student_img[$key]) {
                $pathname = Storage::disk('student')->put('', $request->student_img[$key]);

                $student->update(['picture' => $pathname]);
            }
        }

        $students = Student::find($request->student_id);

        return $this->sendResponse(StudentResourcePure::collection($students), 'image uploaded successfully', 200);
    }

    public function addCampusCodeToAdmissionId()
    {
        $students = Student::all();

        foreach ($students as $student) {
            if (!preg_match("/[a-z]/i", $student->admission_id)) {
                $campus_code = Campus::find($student->campus_id)->code;
                $admission_id = $campus_code . '-' . $student->admission_id;

                $student->update(['admission_id' => $admission_id]);
            } else {
                continue;
            }
        }

        return $this->sendResponse([], 'Admission Id Successfully changed', 200);
    }

    public function removeCampusCodeToAdmissionId()
    {
        $students = Student::all();

        foreach ($students as $student) {
            if (preg_match("/[a-z]/i", $student->admission_id)) {
                $campus_code = Campus::find($student->campus_id)->code;
                $admission_id = substr($student->admission_id, 3);
                $admission_id = $campus_code . $admission_id;

                $student->update(['admission_id' => $admission_id]);
            } else {
                continue;
            }
        }

        return $this->sendResponse([], 'Admission Id Successfully changed', 200);
    }

    public function getNotificationIds(StudentNotificationIdsRequest $request)
    {

        $notification_ids = Student::whereIn('campus_id', $request->campus_ids)->pluck('notification_id')->toArray();
        return $this->sendResponse($notification_ids, '');
    }
}
