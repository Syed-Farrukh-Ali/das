<?php

namespace App\Repository;

use App\Http\Resources\FeeChallanDetailResource;
use App\Http\Resources\StudentResource;
use App\Models\Campus;
use App\Models\FeeChallan;
use App\Models\FeeChallanDetail;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\StudentClass;
use App\Repository\Interfaces\StudentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentRepository extends BaseRepository implements StudentRepositoryInterface
{
    /**$userRepository
     * ProfileRepository constructor.
     *
     * @param User $model
     */
    public function __construct(Student $model)
    {
        parent::__construct($model);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return StudentResource::collection(Student::all());
    }

    public function campusStudents($campus_id)
    {
        //     return  Student::find(319)->studentClass->name;
        //     return new StudentResource( Student::find(319));

        return StudentResource::collection(Campus::find($campus_id)->students()->with(['studentLiableFees', 'studentClass', 'globalSection'])->latest()->get());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getByAdmissionId($adm_id)
    {
        return StudentResource::collection(Student::where('admission_id', $adm_id)->get());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function storeWithFee(Request $request)
    {

        DB::beginTransaction();
        try {
            $campus_code = Campus::find($request->campus_id)->code ?? Str::random(3);
            $time_stamp = Carbon::now()->format('d-m-Y-H-i-s');
            $notification_id = $campus_code . '-' . Str::random(26) . '-' . $time_stamp;
            $student = Student::create([
                'session_id' => $request->year_id,
                'campus_id' => $request->campus_id,
                'student_class_id' => $request->student_class_id,
                'education_type' => $request->education_type,
                'course_id' => $request->course_id,
                'hostel_id' => $request->hostel_id,
                'concession_id' => $request->concession_id,
                'concession_remarks' => $request->concession_remarks,
                'vehicle_id' => $request->vehicle_id,
                'global_section_id' => $request->global_section_id,
                'name' => $request->name,
                'father_name' => $request->father_name,
                'employee_id' => $request->employee_id ? $request->employee_id : null,
                'cnic_no' => $request->cnic_no,
                'father_cnic' => $request->father_cnic,
                'dob' => $request->dob,
                'religion' => $request->religion,
                'gender' => $request->gender,
                'mobile_no' => $request->mobile_no,
                'phone' => $request->phone,
                'address' => $request->address,
                'remarks' => $request->remarks,
                'previous_school' => $request->previous_school,
                'class_left' => $request->class_left,
                'leaving_date' => $request->leaving_date,
                'shift' => $request->shift,
                'Joining_date' => date('Y-m-d'),
            ]);
            $student->update([
                'registration_id' => $student->id,
                'notification_id' => $notification_id
            ]);
            $student->setLiableFees($request->toArray());

            if ($request->status == 2) {
                $student->update(['status' => 3]);

                $stdLiableFees = $student->studentLiableFees;
                $date = date('Y-m-d');

                $old = FeeChallan::max('challan_no') ?? 1;
                $new_challan_no = $old + 1;
                $feeChallan = $student->FeeChallans()->create([
                    'campus_id' => $student->campus_id,
                    'challan_no' => $new_challan_no,
                    'payable' => null, //first fee chalan detail wil be created & then sum of amount wil be here
                    'due_date' => $request->due_date ?? $date,
                    'issue_date' => $date,
                ]);
                // monthly defined fees is creating,
                foreach ($request->fee_month as $key => $month) {
                    foreach ($stdLiableFees as $key => $stdLiableFee) {
                        $feeChallan->feeChallanDetails()->create([
                            'student_id' => $student->id,
                            'amount' => $stdLiableFee->amount,
                            'fee_month' => $month,
                            'fee_name' => $stdLiableFee->feesType->name,
                            'campus_id' => $student->campus_id,
                            'fees_type_id' => $stdLiableFee->fees_type_id,
                        ]);
                    }
                }

                // prospectus fees is defining
                $feeChallan->feeChallanDetails()->create([
                    'student_id' => $student->id,
                    'amount' => $request->registration_fee ?? 3333,
                    'fee_month' => $request->fee_month[0],
                    'fee_name' => 'REGISTRATION_FEE',
                    'campus_id' => $student->campus_id,
                    'fees_type_id' => 2,
                ]);

                $feeChallan->feeChallanDetails()->create([
                    'student_id' => $student->id,
                    'amount' => $request->prospectus_fee ?? 3300,
                    'fee_month' => $request->fee_month[0],
                    'fee_name' => 'PROSPECTUS',
                    'campus_id' => $student->campus_id,
                    'fees_type_id' => 1,
                ]);

                $feeChallan->feeChallanDetails()->create([
                    'student_id' => $student->id,
                    'amount' => $request->admission_fee ?? 3300,
                    'fee_month' => $request->fee_month[0],
                    'fee_name' => 'ADMISSION FEE',
                    'campus_id' => $student->campus_id,
                    'fees_type_id' => 3,
                ]);

                $feeChallan->feeChallanDetails()->create([
                    'student_id' => $student->id,
                    'amount' => $request->annual_fund_fee ?? 2500,
                    'fee_month' => $request->fee_month[0],
                    'fee_name' => 'ANNUAL FUND',
                    'campus_id' => $student->campus_id,
                    'fees_type_id' => 5,
                ]);

                if ($request->hostel_id) {
                    // hostel admission fees
                    $feeChallan->feeChallanDetails()->create([
                        'student_id' => $student->id,
                        'amount' => $request->hostel_admission_fee ?? 3300,
                        'fee_month' => $request->fee_month[0],
                        'fee_name' => 'HOSTEL ADMISSION FEE',
                        'campus_id' => $student->campus_id,
                        'fees_type_id' => 6,
                    ]);
                }
                $feeChallan->update([
                    'payable' => $feeChallan->feeChallanDetails()->sum('amount'),
                ]);
            }
            if ($request->status == 1) {
                $student->update(['status' => 1]);
            }

            $class = StudentClass::find($request->student_class_id);

            if ($class->subjects->count() > 0) {
                $subject_ids = $class->subjects()->pluck('subject_id')->unique()->toArray();

                $student->subjects()->sync($subject_ids);
            }

            $student->load('feeChallans.campus.printAccountNos', 'studentLiableFees.feesType');
        } catch (\Throwable $e) {
            DB::rollBack();

            return $e->getMessage();
        }
        DB::commit();

        return new StudentResource($student);
    }


    public function show(Student $student)
    {
        $fee_month = Carbon::today()->subMonth(11)->firstOfMonth();
        $challan_detail_of_past_6month = FeeChallanDetail::with('feeChallan')->where(['student_id' => $student->id])->whereDate('fee_month', '>=', $fee_month)->get();

        $past_6_month_challan_details = FeeChallanDetailResource::collection($challan_detail_of_past_6month);

        return new StudentResource($student->load('studentLiableFees.feesType', 'session', 'globalSection', 'studentClass', 'campus', 'subjects'));
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function update(Request $request, Student $student)
    {
        DB::beginTransaction();
        try {
            $student->update([
                'name' => $request->name,
                'father_cnic' => $request->father_cnic,
                'previous_school' => $request->previous_school,
                'religion' => $request->religion,
                'cnic_no' => $request->cnic_no,
                'father_name' => $request->father_name,
                'employee_id' => $request->employee_id ? $request->employee_id : null,
                'class_left' => $request->class_left,
                'global_section_id' => $request->global_section_id,
                'gender' => $request->gender,
                'leaving_date' => $request->leaving_date,
                'dob' => $request->dob,
                'remarks' => $request->remarks,
                'mobile_no' => $request->mobile_no,
                'phone' => $request->phone,
                'address' => $request->address,
                'shift' => $request->shift,
                'session_id' => $request->year_id,
                'campus_id' => $request->campus_id,
                'student_class_id' => $request->student_class_id,
                'education_type' => $request->education_type,
                'course_id' => $request->course_id,
                'hostel_id' => $request->hostel_id,
                'concession_id' => $request->concession_id,
                'concession_remarks' => $request->concession_remarks,
                'vehicle_id' => $request->vehicle_id,
                'status' => $request->status,
                'struck_off_date' => $request->struck_off_date,
                'Joining_date' => Carbon::parse($request->Joining_date)->format('Y-m-d'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }
        DB::commit();

        return new StudentResource($student);
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json('student successfully deleted');
    }

    public function changeStatus(Request $request)
    {
        $student = Student::find($request->student_id);
        $admission_id = $student->admission_id; // note: admission table is only used for generating unique incrementd number
        if ($student->admission_id == null and $request->status == 2) {
            $student = _studentAdmission($student, date('Y-m-d'));
        } else {
            // code...
            $student->update(['status' => $request->status]);
        }

        return new StudentResource($student);
    }

    /**
     * @param $registration_id
     * @return \Illuminate\Http\Response
     */
    public function changeConcession(Request $request)
    {
        $student = Student::find($request->student_id);
        $updated = $student->update(['concession_id' => $request->concession_id]);
        if ($updated) {
            return true;
        }

        return false;
    }

    /**
     * @param $registration_id
     * @return \Illuminate\Http\Response
     */
    public function studentFilterList(Request $request)
    {
        $session_id = $request->year_id;
        $campus_id = $request->campus_id;
        $std_cls_id = $request->student_class_id;
        $education_type = $request->education_type;
        $global_section_id = $request->global_section_id;
        $status = $request->status;

        $students = Student::where(function ($query) use ($campus_id) {
            return  $campus_id != null ? $query->where('campus_id', $campus_id) : '';
        })
            ->where(function ($query) use ($std_cls_id) {
                return  $std_cls_id != null ? $query->where('student_class_id', $std_cls_id) : '';
            })
            ->where(function ($query) use ($education_type) {
                return  $education_type != null ? $query->where('education_type', $education_type) : '';
            })
            ->where(function ($query) use ($global_section_id) {
                return  $global_section_id != null ? $query->where('global_section_id', $global_section_id) : '';
            })
            ->where(function ($query) use ($session_id) {
                return  $session_id != null ? $query->where('session_id', $session_id) : '';
            })
            ->where(function ($query) use ($status) {
                return  $status != null ? $query->where('status', $status) : '';
            })
            ->orderBy('admission_id', 'asc')->get();
        $students->load('studentClass', 'session', 'globalSection', 'subjects');

        return StudentResource::collection($students);
    }

    public function allRegisteredOrAdmitted(Request $request) // registered+
    {
        $std_cls_id = $request->student_class_id;
        $education_type = $request->education_type;
        $global_section_id = $request->global_section_id;
        $session_id = $request->year_id;

        $students = Student::where('campus_id', $request->campus_id)->whereIn('status', $request->status)
            ->where(function ($query) use ($std_cls_id) {
                return  $std_cls_id != null ? $query->where('student_class_id', $std_cls_id) : '';
            })
            ->where(function ($query) use ($education_type) {
                return  $education_type != null ? $query->where('education_type', $education_type) : '';
            })
            ->where(function ($query) use ($global_section_id) {
                return  $global_section_id != null ? $query->where('global_section_id', $global_section_id) : '';
            })
            ->where(function ($query) use ($session_id) {
                return  $session_id != null ? $query->where('session_id', $session_id) : '';
            })

            ->latest()->get();
        $students->load('studentClass', 'session', 'globalSection', 'subjects');

        return StudentResource::collection($students);
    }
}
