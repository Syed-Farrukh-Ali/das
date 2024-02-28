<?php

namespace App\Repository;

use App\Http\Resources\FeeChallanDetailResource;
use App\Http\Resources\FeeChallanResource;
use App\Http\Resources\StudentResource;
use App\Models\FeeChallan;
use App\Models\FeeChallanDetail;
use App\Models\FeesType;
use App\Models\Student;
use App\Repository\Interfaces\FeesGeneratorRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\FeeStructure;
use App\Models\LateFeeFine;
use App\Models\Setting;


class FeesGeneratorRepository extends BaseRepository implements FeesGeneratorRepositoryInterface
{
    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function __construct(Student $model)
    {
        parent::__construct($model);
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function CustomFeesGenerator(Request $request, Student $student)
    {
        $old = FeeChallan::max('challan_no') ?? 1;
        $new_challan_no = $old + 1;
        $stdLiableFees = $student->studentLiableFees;

        DB::beginTransaction();
        try {
            $feeChallan = $student->FeeChallans()->create([
                'campus_id' => $student->campus_id,
                'challan_no' => $new_challan_no,
                'payable' => null, //first fee chalan detail wil be created & then sum of amount wil be here
                'due_date' => $request->due_date,
                'issue_date' => $request->issue_date,

            ]);

            if ($request->additional_fee_status) {
                // code...
                for ($i = 0; $i < count($request->fees_type_id); $i++) {
                    $feeChallan->feeChallanDetails()->create([
                        'student_id' => $student->id,
                        'amount' => $request->amount[$i],
                        'fee_month' => substr($request->due_date, 0, 8) . '01', // put 01 on day location in due_date and store it as fee_month, in cas fee month is not given
                        'fee_name' => FeesType::find($request->fees_type_id[$i])->name,
                        'campus_id' => $student->campus_id,
                        'fees_type_id' => $request->fees_type_id[$i],
                    ]);
                }
            }

            if ($request->monthly_fee_status) {
                for ($i = 0; $i < count($request->fee_month); $i++) {
                    foreach ($stdLiableFees as $key => $stdLiableFee) {
                        $feeChallan->feeChallanDetails()->create([
                            'student_id' => $student->id,
                            'amount' => $stdLiableFee->amount,
                            'fee_month' => $request->fee_month[$i],
                            'fee_name' => $stdLiableFee->feesType->name,
                            'campus_id' => $student->campus_id,
                            'fees_type_id' => $stdLiableFee->fees_type_id,
                        ]);
                    }
                }

                $std_3month_challans = FeeChallan::with('feeChallanDetails')->whereStudentId($student->id)->latest()->take(3);

                if ($overdue_challan = $std_3month_challans->where('status', 0)->whereDate('due_date', '<', date('Y-m-d'))->get()) { // if some of the fees are not paid and overdue
                    if ($std_3month_challans->where('status', 0)->get()->pluck('feeChallanDetails')->flatten()->where('fees_type_id', 9)->isEmpty()) { //if it has already a re-admission fee then skip
                        if ($overdue_challan->count() > 1) {
                            // code.. if have more then 1 over due challan then fine him do not think
                            $feeChallan->feeChallanDetails()->create([
                                'student_id' => $student->id,
                                'amount' => 1000,
                                'fee_month' => $request->fee_month[0],
                                'fee_name' => 'RE-ADMISSION FEE',
                                'campus_id' => $student->campus_id,
                                'fees_type_id' => 9,
                            ]);
                        } elseif ($overdue_challan->count() == 1) { // if have 1 over due then check weather a month is passed or not
                            $due_date = Carbon::createFromFormat('Y-m-d', $overdue_challan->pluck('due_date')[0])->month;
                            $today = Carbon::today()->month;
                            if ($due_date != $today) {
                                // code...
                                $feeChallan->feeChallanDetails()->create([
                                    'student_id' => $student->id,
                                    'amount' => 1000,
                                    'fee_month' => $request->fee_month[0],
                                    'fee_name' => 'RE-ADMISSION FEE',
                                    'campus_id' => $student->campus_id,
                                    'fees_type_id' => 9,
                                ]);
                            }
                        }
                    }
                } elseif ($std_3month_challans->where('status', '>', 0)->get()) {
                    $submitted_challan = $std_3month_challans->take(1)->get();
                    $due_date = Carbon::createFromFormat('Y-m-d', $submitted_challan[0]->due_date);
                    $received_Date = Carbon::createFromFormat('Y-m-d', $submitted_challan[0]->received_date);
                    if ($received_Date->gt($due_date)) {
                        if ($due_date->month == $received_Date->month) {
                            $difference = $due_date->diff($received_Date)->days;
                            $feeChallan->feeChallanDetails()->create([
                                'student_id' => $student->id,
                                'amount' => $difference * 10,
                                'fee_month' => $request->fee_month[0],
                                'fee_name' => 'LATE FINE',
                                'campus_id' => $student->campus_id,
                                'fees_type_id' => 16,
                            ]);
                        } else {
                            $feeChallan->feeChallanDetails()->create([
                                'student_id' => $student->id,
                                'amount' => 1000,
                                'fee_month' => $request->fee_month[0],
                                'fee_name' => 'RE-ADMISSION FEE',
                                'campus_id' => $student->campus_id,
                                'fees_type_id' => 9,
                            ]);
                        }
                    }
                }
            }

            $feeChallan->update([
                'payable' => $feeChallan->feeChallanDetails()->sum('amount'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }

        DB::commit();

        $feeChallan->load('feeChallanDetails');

        return new FeeChallanResource($feeChallan);
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function showStdChallans($id)
    {
        $student = Student::find($id);
        $student->load('feeChallans.feeChallanDetails');

        return new StudentResource($student);
    }

    public function showStdChallanHistory(Request $request)
    {
        $student = Student::find($request->student_id);
        $student->load('feeChallans.feeChallanDetails');

        $fee_month = Carbon::today()->subMonth(11)->firstOfMonth();
        $challan_detail_of_past_6month = FeeChallanDetail::with('feeChallan.bank_account')->where(['student_id' => $student->id])->whereDate('fee_month', '>=', $fee_month)->get();
        $past_6_month_challan_details = FeeChallanDetailResource::collection($challan_detail_of_past_6month);

        return $past_6_month_challan_details;
    }

    public function allFeeStdList(Request $request)
    {
        $campus_id = $request->campus_id;
        $std_cls_id = $request->student_class_id;
        $education_type = $request->education_type;
        $section_id = $request->section_id;
        $session_id = $request->year_id;

        $students = Student::where('campus_id', $campus_id)
            ->where('status', 2)
            ->where(function ($query) use ($std_cls_id) {
                return  $std_cls_id != null ? $query->where('student_class_id', $std_cls_id) : '';
            })
            ->where(function ($query) use ($education_type) {
                return  $education_type != null ? $query->where('education_type', $education_type) : '';
            })
            ->where(function ($query) use ($section_id) {
                return  $section_id != null ? $query->where('global_section_id', $section_id) : '';
            })
            ->where(function ($query) use ($session_id) {
                return  $session_id != null ? $query->where('session_id', $session_id) : '';
            })
            ->get();

        return $students->load('studentLiableFees');
    }


    public function AddFine(Request $request, $student, $feeChallan, $new_challan_no, $fee_month)
    {
        $add_fine = false;
        $daysDifference = 0;

        $std_last_month_challan = FeeChallan::where('student_id', $student->id)->latest()->first();

        // if($std_last_month_challan->feeChallanDetails)

        foreach ($std_last_month_challan->feeChallanDetails as $key => $feeDetail) {
            if ($feeDetail->fee_month == $fee_month) {
                return;
            }
        }


        //******* Calculate student fine
        //For Hifz
        $classID = $student->student_class_id;

        if ($student->education_type == 2) {
            $classID = 1;
        }

        $readmission_fees = FeeStructure::where('campus_id', $student->campus_id)
            ->where('student_class_id', $classID)
            ->where('session_id', $student->session_id)
            ->where('fee_type_id', 9)
            ->value('amount');


        if ($std_last_month_challan) {
            if ($std_last_month_challan->status == '0') {
                $add_fine = true;
                $daysDifference = -1;
            } else {
                // Get Late fee fine from DB
                $late_fee_fine = Setting::where('id', '1')->value('late_fee_fine');

                if ($std_last_month_challan->received_date && $std_last_month_challan->due_date) {
                    $received_date = Carbon::parse($std_last_month_challan->received_date);
                    $due_date = Carbon::parse($std_last_month_challan->due_date);

                    // Calculate the difference in days
                    if ($received_date > $due_date) {
                        $daysDifference = $received_date->diffInDays($due_date);
                        $add_fine = true;
                    }
                }
            }
        }

        if ($daysDifference == 0) {
            $add_fine = false;
        }

        // ADD Fine if NEEDED

        if ($add_fine) {

            if (!$feeChallan) {
                $feeChallan = $student->FeeChallans()->create([
                    'campus_id' => $student->campus_id,
                    'session_id' => $student->session_id,
                    'challan_no' => $new_challan_no,
                    'payable' => null, //first fee chalan detail wil be created & then sum of amount wil be here
                    'due_date' => $request->due_date,
                    'issue_date' => $request->issue_date,
                ]);
            }

            // $this->info("Phasany wali line  ");
            if ($daysDifference == -1) {

                $feeChallan->feeChallanDetails()->create([
                    'student_id' => $student->id,
                    'amount' => $readmission_fees,
                    'fee_month' => $request->fee_month[0],
                    'fee_name' => 'RE-ADMISSION FEE',
                    'campus_id' => $student->campus_id,
                    'fees_type_id' => 9,
                ]);
            } else if ($daysDifference > 0) {
                $fine_amount = $late_fee_fine * $daysDifference;

                $feeChallan->feeChallanDetails()->create([
                    'student_id' => $student->id,
                    'amount' => $fine_amount,
                    'fee_month' => $request->fee_month[0],
                    'fee_name' => 'LATE FEE FINE',
                    'campus_id' => $student->campus_id,
                    'fees_type_id' => 8,
                ]);
            }
        }

        return $feeChallan;
    }


    public function feeGenerateByStdList(Request $request)
    {
        $due_date = $request->due_date;
        $issue_date = $request->issue_date;
        $monthly_fee_status = $request->monthly_fee_status;
        $fee_months = $request->fee_month;
        // $fee_month = $request->fee_month;
        $fees_type_ids = $request->fees_type_id;
        $fee_amounts = $request->amount;

        // return substr($request->due_date, 0, 8) . '01';

        if ($monthly_fee_status == 0) {
            $fee_months = [0 => substr($request->due_date, 0, 8) . '01']; // if monthly fee is not generating than code skip additional fee too, so added fee_month
        }

        // $students = Student::whereIn('id', $request->student_id)->get();

        $old = FeeChallan::max('challan_no') ?? 1;
        $new_challan_no = $old + 1;

        // dd($students->pluck('id')->toArray());
        $studentIds = $request->student_id;

        $students = Student::with('feeChallans')->with('feeChallanDetails')->whereIn('id', $studentIds)->get();


        DB::beginTransaction();
        try {
            foreach ($students as $key => $student) {

                $std_liable_fees = $student->studentLiableFees;

                $ch_details = $student->feeChallanDetails;

                $fine_added = false;

                foreach ($fee_months as $month_key => $fee_month) {

                    $feeChallan = null;

                    if ($request->add_fine && !$fine_added) {
                        $feeChallan = $this->AddFine($request, $student, $feeChallan, $new_challan_no, $fee_month);

                        $fine_added = true;
                    }

                    $new_challan_no = $new_challan_no + 1;

                    if ($monthly_fee_status == 1) {

                        foreach ($std_liable_fees as $key => $std_liable_fee) {
                            if ($ch_details->where('fee_month', $fee_month)->where('fees_type_id', $std_liable_fee->fees_type_id)->count()) { // skipping this challan detail if there is already a fee of this type for this month
                                continue;
                            }

                            if (!$feeChallan) {
                                $feeChallan = $student->FeeChallans()->create([
                                    'campus_id' => $student->campus_id,
                                    'session_id' => $student->session_id,
                                    'challan_no' => $new_challan_no,
                                    'payable' => null, //first fee chalan detail wil be created & then sum of amount wil be here
                                    'due_date' => $due_date,
                                    'issue_date' => $issue_date,
                                ]);
                            }

                            $feeChallan->feeChallanDetails()->create([
                                'student_id' => $student->id,
                                'amount' => $std_liable_fee->amount,
                                'fee_month' => $fee_month,
                                'fee_name' => $std_liable_fee->FeesType->name,
                                'campus_id' => $student->campus_id,
                                'fees_type_id' => $std_liable_fee->fees_type_id,
                            ]);
                        }
                    }

                    //additional_fee only for one month if we have more than 1 month////////////////////////////////////////////////////
                    if ($month_key == 0 and $fees_type_ids and $request->additional_fee_status == 1) {
                        // if ($fees_type_ids and $request->additional_fee_status == 1) {
                        foreach ($fees_type_ids as $key => $fees_type_id) {
                            if ($ch_details->where('fee_month', substr($request->due_date, 0, 8) . '01')->where('fees_type_id', $fees_type_id)->count()) { // skipping this challan detail if there is already a fee of this type for this month
                                continue;
                            }


                            if (!$feeChallan) {
                                $feeChallan = $student->FeeChallans()->create([
                                    'campus_id' => $student->campus_id,
                                    'session_id' => $student->session_id,
                                    'challan_no' => $new_challan_no,
                                    'payable' => null, //first fee chalan detail wil be created & then sum of amount wil be here
                                    'due_date' => $due_date,
                                    'issue_date' => $issue_date,
                                ]);
                            }

                            $feeChallan->feeChallanDetails()->create([
                                'student_id' => $student->id,
                                'amount' => $fee_amounts[$key],
                                'fee_month' => substr($request->due_date, 0, 8) . '01', // put 01 on day location in due_date and store it as fee_month, in cas fee month is not given
                                'fee_name' => FeesType::find($fees_type_id)->name,
                                'campus_id' => $student->campus_id,
                                'fees_type_id' => $fees_type_id,
                            ]);
                        }
                    }
                    //additional_fee only for one month if we have more than 1 month////////////////////////////////////////////////////
                    if ($feeChallan) {
                        $feeChallan->update(['payable' => $feeChallan->feeChallanDetails()->sum('amount')]);

                        if ($feeChallan->feeChallanDetails()->sum('amount') < 1) {
                            $feeChallan->feeChallanDetails()->forceDelete();
                            $feeChallan->forceDelete();
                            $new_challan_no = $new_challan_no - 1;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // return $stdList;
            DB::rollBack();
            return false;
        }

        DB::commit();

        return true;
    }
    public function feeGenerateByStdListNew(Request $request)
    {
        $due_date = $request->due_date;
        $issue_date = $request->issue_date;
        $monthly_fee_status = $request->monthly_fee_status;
        $fee_months = $request->fee_month;
        $fees_type_ids = $request->fees_type_id;
        $fee_amounts = $request->amount;
        //$fee_discount = $request->discount;
        $fee_amounts1 = $request->discount;

        if ($monthly_fee_status == 0) {
            $fee_months = [0 => substr($request->due_date, 0, 8) . '01']; // if monthly fee is not generating than code skip additional fee too, so added fee_month
        }

        $students = Student::whereIn('id', $request->student_id)->get();

        $old = FeeChallan::max('challan_no') ?? 1;
        $new_challan_no = $old + 1;
        // dd($students->pluck('id')->toArray());

        DB::beginTransaction();
        try {
            foreach ($students as $key => $student) {
                $std_liable_fees = $student->studentLiableFees;
                $fee_challan_ids = $student->feeChallans->pluck('id');
                $ch_details = FeeChallanDetail::whereIn('fee_challan_id', $fee_challan_ids)->whereIn('fee_month', $fee_months)->get();

                foreach ($fee_months as $month_key => $fee_month) {
                    $feeChallan = $student->FeeChallans()->create([
                        'campus_id' => $student->campus_id,
                        'challan_no' => $new_challan_no,
                        'payable' => null, //first fee chalan detail wil be created & then sum of amount wil be here
                        'due_date' => $request->due_date,
                        'issue_date' => $request->issue_date,
                    ]);
                    $new_challan_no = $new_challan_no + 1;

                    if ($monthly_fee_status == 1) {
                        foreach ($std_liable_fees as $key => $std_liable_fee) {
                            if ($ch_details->where('fee_month', $fee_month)->where('fees_type_id', $std_liable_fee->fees_type_id)->count()) { // skipping this challan detail if there is already a fee of this type for this month
                                continue;
                            }
                            $feeChallan->feeChallanDetails()->create([
                                'student_id' => $student->id,
                                'amount' => $std_liable_fee->amount - ($std_liable_fee->amount * ($fee_amounts1 / 100)),
                                'fee_month' => $fee_month,
                                //'month'=>$std_liable_fee->amount-($std_liable_fee->amount*($fee_amounts1/100)),
                                'fee_name' => $std_liable_fee->FeesType->name,
                                'campus_id' => $student->campus_id,
                                'fees_type_id' => $std_liable_fee->fees_type_id,
                            ]);
                        }
                        ////////////
                        if ($request->add_fine == 1) {
                            $std_3month_challans = FeeChallan::with('feeChallanDetails')->whereStudentId($student->id)->latest()->take(3);

                            if ($overdue_challan = $std_3month_challans->where('status', 0)->whereDate('due_date', '<', date('Y-m-d'))->get()) { // if some of the fees are not paid and overdue
                                if ($std_3month_challans->where('status', 0)->get()->pluck('feeChallanDetails')->flatten()->where('fees_type_id', 9)->isEmpty()) { //if it has already a re-admission fee then skip
                                    if ($overdue_challan->count() > 1) {
                                        // code.. if have more then 1 over due challan then fine him do not think
                                        $feeChallan->feeChallanDetails()->create([
                                            'student_id' => $student->id,
                                            'amount' => 1000,
                                            'fee_month' => $request->fee_month[0],
                                            'fee_name' => 'RE-ADMISSION FEE',
                                            'campus_id' => $student->campus_id,
                                            'fees_type_id' => 9,
                                        ]);
                                    } elseif ($overdue_challan->count() == 1) { // if have 1 over due then check weather a month is passed or not
                                        $due_date = Carbon::createFromFormat('Y-m-d', $overdue_challan->pluck('due_date')[0])->month;
                                        $today = Carbon::today()->month;
                                        if ($due_date != $today) {
                                            // code...
                                            $feeChallan->feeChallanDetails()->create([
                                                'student_id' => $student->id,
                                                'amount' => 1000,
                                                'fee_month' => $request->fee_month[0],
                                                'fee_name' => 'RE-ADMISSION FEE',
                                                'campus_id' => $student->campus_id,
                                                'fees_type_id' => 9,
                                            ]);
                                        }
                                    }
                                }
                            } elseif ($std_3month_challans->where('status', '>', 0)->get()) {
                                $submitted_challan = $std_3month_challans->take(1)->get();
                                $due_date = Carbon::createFromFormat('Y-m-d', $submitted_challan[0]->due_date);
                                $received_Date = Carbon::createFromFormat('Y-m-d', $submitted_challan[0]->received_date);
                                if ($received_Date->gt($due_date)) {
                                    if ($due_date->month == $received_Date->month) {
                                        $difference = $due_date->diff($received_Date)->days;
                                        $feeChallan->feeChallanDetails()->create([
                                            'student_id' => $student->id,
                                            'amount' => $difference * 10,
                                            'fee_month' => $request->fee_month[0],
                                            'fee_name' => 'LATE FINE',
                                            'campus_id' => $student->campus_id,
                                            'fees_type_id' => 16,
                                        ]);
                                    } else {
                                        $feeChallan->feeChallanDetails()->create([
                                            'student_id' => $student->id,
                                            'amount' => 1000,
                                            'fee_month' => $request->fee_month[0],
                                            'fee_name' => 'RE-ADMISSION FEE',
                                            'campus_id' => $student->campus_id,
                                            'fees_type_id' => 9,
                                        ]);
                                    }
                                }
                            }
                        }
                        //////////////
                    }

                    //additional_fee only for one month if we have more than 1 month////////////////////////////////////////////////////
                    if ($month_key == 0 and $fees_type_ids and $request->additional_fee_status == 1) {
                        foreach ($fees_type_ids as $key => $fees_type_id) {
                            if ($ch_details->where('fee_month', substr($request->due_date, 0, 8) . '01')->where('fees_type_id', $fees_type_id)->count()) { // skipping this challan detail if there is already a fee of this type for this month
                                continue;
                            }
                            $feeChallan->feeChallanDetails()->create([
                                'student_id' => $student->id,
                                'amount' => $fee_amounts[$key],
                                //'month'=>$fee_amounts[$key]-($fee_amounts[$key]*($fee_amounts1/100)),
                                'fee_month' => substr($request->due_date, 0, 8) . '01', // put 01 on day location in due_date and store it as fee_month, in cas fee month is not given
                                'fee_name' => FeesType::find($fees_type_id)->name,
                                'campus_id' => $student->campus_id,
                                'fees_type_id' => $fees_type_id,
                            ]);
                        }
                    }
                    //additional_fee only for one month if we have more than 1 month////////////////////////////////////////////////////

                    $feeChallan->update(['payable' => $feeChallan->feeChallanDetails()->sum('amount')]);
                    if ($feeChallan->feeChallanDetails()->sum('amount') < 1) {
                        $feeChallan->forceDelete();
                        $new_challan_no = $new_challan_no - 1;
                    }
                }
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e);

            return false;
        }
        DB::commit();

        return true;
    }
}
