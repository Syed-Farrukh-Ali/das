<?php

namespace App\Http\Controllers\Api\StudentReports;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StudentReport\StudentCheckListRequest;
use App\Http\Resources\CampusResource;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\FeeChallanDetailResource;
use App\Http\Resources\FeeChallanResourceCopy;
use App\Http\Resources\SalaryResource;
use App\Http\Resources\StudentLiableFeeResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\StudentResourcePure;
use App\Http\Resources\StudentResourceShort;
use App\Models\Campus;
use App\Models\Concession;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\FeeChallan;
use App\Models\FeeChallanDetail;
use App\Models\FeesType;
use App\Models\GlobalSection;
use App\Models\Hostel;
use App\Models\Session;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentLiableFee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentReportController extends BaseController
{
    public function feeBill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admission_id' => 'nullable|exists:students,admission_id',
            'registration_id' => 'nullable|exists:students,registration_id',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        if ($request->admission_id) {
            $challans = Student::where('admission_id', $request->admission_id)->first()->feeChallans()->where('status', 0)->where('parent_id', null)->get();
            $challans->load('feeChallanDetails', 'student.studentClass', 'student.globalSection');
            $student = Student::where('admission_id', $request->admission_id)->first();
            $data = [
                'student' => new StudentResource($student->load('registrationcard', 'session', 'hostel')),
                'challans' => FeeChallanResourceCopy::collection($challans),
            ];

            return $this->sendResponse($data, []);
        }
        if ($request->registration_id) {
            $challans = Student::where('registration_id', $request->registration_id)->first()->feeChallans()->where('status', 0)->where('parent_id', null)->get();
            $challans->load('feeChallanDetails', 'student', 'student.studentClass', 'student.globalSection');
            $student = Student::where('registration_id', $request->registration_id)->first();
            $data = [
                'student' => new StudentResource($student->load('registrationcard', 'session', 'hostel')),
                'challans' => FeeChallanResourceCopy::collection($challans),
            ];

            return $this->sendResponse($data, []);
        }
    }

    public function newAdmissionReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date_format:Y-m-d',
            'to_date' => 'required|date_format:Y-m-d',
            'year_id' => 'required|exists:sessions,id',
            'fee_wise' => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $students = Student::whereBetween('Joining_date', [$request->from_date, $request->to_date])->where(['session_id' => $request->year_id])->get();
        $campuses = Campus::find($students->pluck('campus_id'));
        $classes = StudentClass::find($students->pluck('student_class_id'));
        $report = [];

        if ($request->fee_wise) {
            foreach ($campuses as $key1 => $campus) {
                array_push($report, [
                    'campus' => $campus->name,
                    'total_fees' => StudentLiableFee::whereIn('student_id', $students->where('campus_id', $campus->id)->pluck('id')->toArray())->sum('amount'),
                    'class' => [],
                ]);
                foreach ($classes as $key2 => $class) {
                    //    if ($students->where('campus_id',$campus->id)->where('student_class_id',$class->id)->isNotEmpty()) {
                    $cls_students = Student::whereBetween('Joining_date', [$request->from_date, $request->to_date])->where(['session_id' => $request->year_id])->where('campus_id', $campus->id)
                        ->where('student_class_id', $class->id)->where('gender', 'male')->get();
                    array_push($report[$key1]['class'], [
                        'name' => $class->name,
                        'students' => [],

                    ]);
                    foreach ($cls_students as $key3 => $std) {
                        array_push($report[$key1]['class'][$key2]['students'], [
                            'name' => $std->name,
                            'father_name' => $std->father_name,
                            'Joining_date' => $std->Joining_date,
                            'admission_id' => $std->admission_id,
                            'total_fees' => $std->studentLiableFees()->sum('amount'),
                        ]);
                    } //foreach
                } //foreach
            }
        } else {
            foreach ($campuses as $key1 => $campus) {
                array_push($report, [
                    'campus' => $campus->name,
                    'total_admission' => $students->where('campus_id', $campus->id)->count(),
                    'class' => [],
                ]);
                foreach ($classes as $key2 => $class) {
                    if ($students->where('campus_id', $campus->id)->where('student_class_id', $class->id)->isNotEmpty()) {
                        array_push($report[$key1]['class'], [
                            'name' => $class->name,
                            'male_students' => Student::whereBetween('Joining_date', [$request->from_date, $request->to_date])->where(['session_id' => $request->year_id])->where('campus_id', $campus->id)
                                ->where('student_class_id', $class->id)->where('gender', 'male')->count(),
                            'female_students' => Student::whereBetween('Joining_date', [$request->from_date, $request->to_date])->where(['session_id' => $request->year_id])->where('campus_id', $campus->id)
                                ->where('student_class_id', $class->id)->where('gender', 'female')->count(),
                        ]);
                    } //foreach
                } //if
            } //foreach
        } //else

        return ['session' => Session::find($request->year_id), 'report' => $report];
    }

    public function feeDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admission_id' => 'nullable|exists:students,admission_id',
            'registration_id' => 'nullable|exists:students,registration_id',
            'year_id' => 'required|exists:sessions,id',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        if ($request->admission_id) {
            $student = Student::with('campus', 'studentClass', 'globalSection', 'session')->where('admission_id', $request->admission_id)->get()->first();
        }
        $year_id = $request->year_id;
        if ($request->registration_id) {
            $student = Student::where('registration_id', $request->registration_id)->get()->first();
        }
        $challan = FeeChallan::with(['voucher' => function ($q) use ($year_id) {
            $q->where('session_id', $year_id);
        }])->where('student_id', $student->id)->whereIn('status', [1, 2])->get();


        $challan_details = FeeChallanDetail::with('feeChallan.bank_account:id,bank_name,account_head', 'feeChallan.voucher:id,voucher_no,date')->whereIn('fee_challan_id', $challan->pluck('id'))->get();
        $data = [
            'student' => new StudentResourcePure($student),
            'total_amount' => $challan_details->sum('amount'),
            'fee_challan_detail' => $challan_details
        ];

        return $this->sendResponse($data, []);
    }

    public function hostelStudentReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => 'required|exists:sessions,id',
            'hostel_id' => 'nullable|exists:hostels,id',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $hostel = Hostel::find($request->hostel_id);
        $session = Session::find($request->year_id);
        $student_ids = Student::where('session_id', $session->id)->where('hostel_id', $hostel->id)->pluck('id');
        $hostel_fees = StudentLiableFee::whereIn('student_id', $student_ids)->where('fees_type_id', 7)->get();
        $hostel_fees->load('student.studentClass', 'student.globalSection', 'student.session');
        $total_fee = $hostel_fees->sum('amount');

        $data = [
            'total_fee_amount' => $total_fee,
            'hostel' => $hostel,
            'session' => $session,
            'hostel_fees' => StudentLiableFeeResource::collection($hostel_fees),
        ];

        return $this->sendResponse($data, []);
    }

    public function feeConcession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => 'required|exists:sessions,id',
            'concession_id' => 'nullable|exists:concessions,id',
            'fees_type_id' => 'nullable|exists:fees_types,id',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $session = Session::find($request->year_id);
        $concession = Concession::find($request->concession_id);
        $fees_type = FeesType::find($request->fees_type_id);
        $students = Student::with('studentLiableFees')->where('session_id', $request->year_id)->where('concession_id', $request->concession_id)->get();
        $std_ids = $students->pluck('id');
        $stdLiablefees = StudentLiableFee::with('student.studentClass', 'student.globalSection')->whereIn('student_id', $std_ids)->where(['fees_type_id' => $request->fees_type_id])->get();
        $data = [
            'session' => $session,
            'concession' => $concession,
            'fees_type' => $fees_type,
            'total_concession' => $sumCA = $stdLiablefees->sum('concession_amount'),
            'total_amount' => $sumTA = $stdLiablefees->sum('amount') + $stdLiablefees->sum('concession_amount'),
            'total_concession_percentage' => ($sumCA / ($sumTA + 1)) * 100,
            'report' => StudentLiableFeeResource::collection($stdLiablefees),
        ];

        return $this->sendResponse($data, []);
        foreach ($students as $key => $student) {
            array_push($report, [
                'student' => $student,
                'fee' => $student->studentLiableFees()->where('fees_type_id', $request->fee_type_id)->first(),
            ]);
        }
    }

    public function registerStaffList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => 'required|exists:sessions,id',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $session = Session::find($request->year_id);
        $year = substr($session->year, 0, 4);
        $emp = Employee::whereYear('created_at', $year)->where('status', 1)->get();
        $emp_male = $emp->where('gender', 'Male');
        $emp_female = $emp->where('gender', 'Female');
        $data = [
            'male_employee' => EmployeeResource::collection($emp_male->load('designation')),
            'female_employee' => EmployeeResource::collection($emp_female->load('designation')),
        ];

        return $this->sendResponse($data, []);
    }

    public function staffList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campus_id' => 'required|exists:campuses,id',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $campus = Campus::find($request->campus_id);
        $employee = $campus->employees()->where('job_status_id', '1')->get();
        $employee->load('designation', 'bankAccount');

        return $this->sendResponse($employee, [], 200);
    }

    public function staffListOverall(Request $request)
    {
        $gender = null;
        if ($request->male) {
            $gender = 'Male';
            $Employee = Employee::with(['campus', 'designation'])->where(['status' => 2, 'gender' => $gender]);
        } elseif ($request->female) {
            $gender = 'Female';
            $Employee = Employee::with(['campus', 'designation'])->where(['status' => 2, 'gender' => $gender]);
        } elseif ($request->both) {
            $Employee = Employee::with(['campus', 'designation'])->where(['status' => 2]);
        }

        if ($request->joining_date_wise) {
            $data = $Employee->where('status', 2)->orderBy('joining_date', 'DESC')->get();
        } elseif ($request->date_of_birth_wise) {
            // code...
            $data = $Employee->where('status', 2)->where('job_status_id', 1)->orderBy('dob', 'DESC')->get();
        }

        if ($request->retired_staff) {
            // code...
            $data = $Employee->where('status', 2)->whereIn('job_status_id', [1, 2])->orderBy('dob', 'DESC')->get();
        } elseif ($request->struck_off) {
            $data = $Employee->where('status', 2)->where('job_status_id', 4)->orderBy('dob', 'DESC')->get();
        } elseif ($request->transfered) {
            $data = $Employee->where('status', 2)->where('job_status_id', 3)->orderBy('dob', 'DESC')->get();
        } elseif ($request->in_service_staff) {
            // code...in_service_staff
            $data = $Employee->where('status', 2)->where('job_status_id', 1)->orderBy('joining_date', 'DESC')->get();
        }

        return $this->sendResponse(EmployeeResource::collection($data), []);
    }

    public function demandPaySheet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'campus_id' => 'nullable|exists:campuses,id',
            'exclude_class_four' => 'required|boolean',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        if ($request->exclude_class_four) {
            $employee = Employee::where(['campus_id' => $request->campus_id, 'job_status_id' => 1, 'status' => 2])->where('designation_id', '!=', 7)->get();
        } else {
            $employee = Employee::where(['campus_id' => $request->campus_id, 'job_status_id' => 1, 'status' => 2])->get();
        }

        $data = [
            'campus' => new CampusResource(Campus::find($request->campus_id)),
            'date' => $request->date,
            'employee' => EmployeeResource::collection($employee->load('jobStatus', 'designation', 'payScale')),
        ];

        return $this->sendResponse($data, []);
    }

    public function monthlyPaySheet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campus_id' => 'nullable|exists:campuses,id',
            'salary_month' => [
                'nullable', 'date_format:Y-m-d',
                function ($student, $salary_month, $fail) {
                    if (substr($salary_month, -2) != '01') {
                        $fail('Oops! something wrong with fee month');
                    }
                },
            ],
            'payment_type' => 'required|string',
            'exclude_class_four' => 'required|boolean',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        if ($request->campus_id) {
            if ($request->payment_type == 'CASH') {
                $employeeSalary = EmployeeSalary::where([
                    'campus_id' => $request->campus_id,
                    'salary_month' => $request->salary_month,
                ])->where('bank_account_id', 1)->get();
            } elseif ($request->payment_type == 'BANK') {
                $employeeSalary = EmployeeSalary::where([
                    'campus_id' => $request->campus_id,
                    'salary_month' => $request->salary_month,
                ])->where('bank_account_id', '!=', 1)->get();
            }
        } else {
            if ($request->payment_type == 'CASH') {
                $employeeSalary = EmployeeSalary::where([
                    'salary_month' => $request->salary_month,
                ])->where('bank_account_id', 1)->get();
            } elseif ($request->payment_type == 'BANK') {
                $employeeSalary = EmployeeSalary::where([
                    'salary_month' => $request->salary_month,
                ])->where('bank_account_id', '!=', 1)->get();
            }
        }

        $data = [
            'salay_month' => $request->salary_month,
            'employee_salary' => SalaryResource::collection($employeeSalary->load('employee.jobStatus')),
        ];

        return $this->sendResponse($data, []);
    }

    public function studentDueFee(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'session_wise' => 'nullable|boolean',
            'class_wise' => 'nullable|boolean',
            'campus_wise' => 'nullable|boolean',
            'section_wise' => 'nullable|boolean',
            ///////////////////////////////////
            'year_id' => 'required|exists:sessions,id',
            'campus_id' => 'nullable|exists:campuses,id',
            'student_class_id' => 'nullable|exists:student_classes,id',
            'global_section_id' => 'nullable|exists:global_sections,id',
            ////////////////////////////////////
            'male' => 'nullable|boolean',
            'female' => 'nullable|boolean',
            'both' => 'nullable|boolean',
            ///////////////////////////////////////
            'student_status' => ['nullable', Rule::In(1, 2, 3, 4, 5, 6, 7)],
            'type_of_fees' => 'nullable|integer|min:0|max:50',
            'over_due' => 'nullable|integer|min:0|max:1',
            ///////////////////////////////////////
            // 'start_date' => 'nullable|date|date_format:Y-m-d',
            // 'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
            'education_type' => 'required|integer',

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        // return $this->sendResponse($request->education_type, 'student due fees', 200);


        $wise_sum = $request->session_wise + $request->class_wise + $request->campus_wise + $request->section_wise;
        // $wise_sum should be equal to 1 to assure that only one value is selected
        if ($wise_sum !== 1) {
            return $this->sendError('please select one option from given 4 options', [], 422);
        }
        $gender_sum = $request->male + $request->female + $request->both;
        // $wise_sum should be equal to 1 to assure that only one value is selected
        if ($gender_sum !== 1) {
            return $this->sendError('please select one option from given 3 gender options', [], 422);
        }
        if ($request->male) {
            $gender = 'Male';
        } elseif ($request->female) {
            $gender = 'Female';
        } else {
            $gender = null;
        }

        //        $start_month = Carbon::parse($request->start_date)->format('m');
        //        $start_year = Carbon::parse($request->start_date)->format('Y');
        //        $end_month = Carbon::parse($request->end_date)->format('m');
        //        $end_year = Carbon::parse($request->end_date)->format('Y');
        //
        //        $due_fee_challans = FeeChallan::where('status', 0)
        //            ->when($request->over_due, fn ($query) => $query->where('due_date', '<', Carbon::today()))
        //            ->when($request->start_date, function ($query) use ($request,$start_month,$start_year) {
        //                return $query->whereMonth('issue_date','>=',$request->start_date);
        //            })
        //            ->when($request->end_date, function ($query) use ($request) {
        //                return $query->whereDate('issue_date','<=',$request->end_date);
        //            })
        //            ->get();

        $fee_type = $request->type_of_fees;

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $over_due = $request->over_due;


        // if ($request->start_date == '-01') {
        //     $start_date = '2010-01-01';
        // }

        // if ($request->end_date == '-01') {
        //     $end_date = '2035-01-01';
        // }

        
        


        $def_students = Student::with([
            'feeChallans' => function ($query) use ($over_due) {
                $query->where('status', 0)
                    ->when($over_due, fn ($query) => $query->where('due_date', '<', Carbon::today()));
            },
            'feeChallans.feeChallanDetails' => function ($query) use ($fee_type, $start_date, $end_date) {
                $query
                    ->when($start_date != "-01", function ($query) use ($start_date) {
                        return $query->whereDate('fee_month', '>=', $start_date);
                    })
                    ->when($end_date != "-01", function ($query) use ($end_date) {
                        return $query->whereDate('fee_month', '<=', $end_date);
                    })
                    ->when($fee_type, fn ($sub_query) => $sub_query->where('fees_type_id', $fee_type));
            },
            'campus',
            'studentClass',
            'globalSection'
        ])
            // ->where('education_type', $request->education_type)
            ->when($request->education_type != 0, fn ($query) => $query->where('education_type', $request->education_type))
            ->when($request->campus_id, fn ($query) => $query->where('campus_id', $request->campus_id))
            ->when($request->year_id, fn ($query) => $query->where('session_id', $request->year_id))
            ->when($request->student_class_id, fn ($query) => $query->where('student_class_id', $request->student_class_id))
            ->when($request->global_section_id, fn ($query) => $query->where('global_section_id', $request->global_section_id))
            ->when($request->student_status, fn ($query) => $query->where('status', $request->student_status))
            ->when($gender, fn ($query) => $query->where('gender', $gender))
            ->get(
                [
                    'id',
                    'session_id',
                    'notification_id',
                    'campus_id',
                    'student_class_id',
                    'course_id',
                    'global_section_id',
                    'registration_id',
                    'admission_id',
                    'name',
                    'father_name',
                    'gender',
                    'mobile_no',

                ]
            );

        $students = [];

        foreach ($def_students as $std) {
            foreach ($std->feeChallans as $feeChallan) {
                if ($feeChallan->feeChallanDetails->count() > 0) {
                    $students[] = $std;
                    break;
                }
            }
        }

        $data = [
            'students' => StudentResourcePure::collection($students),
        ];

        return $this->sendResponse($data, 'student due fees', 200);
    }

    public function studentDueFeePrint(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_ids*' => 'required|exists:students,id',
            'type_of_fees' => 'nullable|integer|min:0|max:50',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        $tree = [];
        $fee_type = $request->type_of_fees;
        $challan = FeeChallan::with(['feeChallanDetails' => function ($query) use ($fee_type) {
            $query->when($fee_type, fn ($sub_query) => $sub_query->where('fees_type_id', $fee_type));
        }])->whereIn('student_id', $request->student_ids)->where('status', 0)->get(['id', 'campus_id', 'payable']);
        $std = Student::with(['campus:id,name,code', 'studentClass:id,name', 'globalSection:id,name', 'feeChallans' => function ($query) {
            return $query->where('status', 0);
        }])->find($request->student_ids);
        ////////////
        $stds = Student::with(['campus:id,name,code', 'studentClass:id,name', 'globalSection:id,name', 'feeChallans' => function ($query) {
            return $query->where('status', 0);
        }])->whereIn('id', $request->student_ids);
        ////////////
        $campus_ids = $std->pluck('campus_id')->unique();
        $campuses = Campus::find($campus_ids);

        $fee_type = $request->type_of_fees;

        foreach ($campuses as $key1 => $campus) {
            $tree[$key1] = [
                'id' => $campus->id,
                'campus_name' => $campus->name,
                'amount' => $challan->where('campus_id', $campus->id)->sum('payable'),
                'classes' => [],
            ];
            $campus_class_ids = $std->where('campus_id', $campus->id)->pluck('student_class_id')->unique();
            $classes = StudentClass::find($campus_class_ids);
            foreach ($classes as $key2 => $class) {
                $tree[$key1]['classes'][$key2] = [
                    'class_name' => $class->name,
                    'sections' => [],
                ];
                $section_ids = $std->where('campus_id', $campus->id)->where('student_class_id', $class->id)->pluck('global_section_id')->unique();
                $sections = GlobalSection::find($section_ids);
                if ($sections) {
                    foreach ($sections as $key3 => $section) {
                        $tree[$key1]['classes'][$key2]['sections'][$key3] = [
                            'section_name' => $section->name,
                            'students' => Student::with(['campus:id,name,code', 'studentClass:id,name', 'globalSection:id,name', 'feeChallans' => function ($query)  use ($fee_type) {
                                return $query->with(['feeChallanDetails' => function ($query) use ($fee_type) {
                                    $query->when($fee_type, fn ($sub_query) => $sub_query->where('fees_type_id', $fee_type));
                                }])->where('status', 0);
                            }])->whereIn('id', $request->student_ids)->where('campus_id', $campus->id)->where('student_class_id', $class->id)->where('global_section_id', $section->id)->get(),
                        ];
                    }
                }
            }
        }

        return $this->sendResponse($tree, 'data for print', 200);
    }
}
