<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Resources\StudentLiableFeeResource;
use App\Models\Session;
// use DB;
use App\Models\Student;
use App\Models\HighestValue;
use App\Models\FeeChallan;
use App\Models\FeeChallanDetail;
use App\Models\Campus;
use App\Models\BankAccount;
use App\Models\BankAccountCategory;
use App\Models\CampusClass;
use App\Models\StudentClass;
use App\Models\ClassSection;
use App\Models\StudentLiableFee;
use App\Models\GeneralLedger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HostelStudentsController extends BaseController
{


    public function sessionWiseFeeReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id'  => 'required|exists:sessions,id',
            'campus_ids' => 'nullable|integer|exists:campuses,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        //GeneralLedger
        $session_id = $request->year_id;
        $campus_id = $request->campus_ids;

        $voucher_no = GeneralLedger::where('campus_id', $campus_id)
            ->where('session_id', $session_id)
            ->where('account_chart_id', '49')
            ->where('sub_account_id', '864')
            ->pluck('voucher_id')->toArray();

        $fee_challan_ids = FeeChallan::whereIn('voucher_id', $voucher_no)
            ->where('campus_id', $campus_id)
            ->pluck('id')->toArray();

        //Waqas Logic ******************///

        // $voucher_no = GeneralLedger::where('session_id', $session_id)
        //     ->where('account_chart_id', '49')
        //     ->where('sub_account_id', '864')
        //     ->pluck('voucher_id')->toArray();

        // $fee_challan_ids = FeeChallan::whereIn('voucher_id', $voucher_no)
        //     ->pluck('id')->toArray();


        //************ Chat GPT Logic */

        $sessionWise_students = FeeChallanDetail::select(
            'fee_challan_details.student_id',
            DB::raw('count(fee_challan_details.student_id) as months'),
            DB::raw('sum(fee_challan_details.amount) as total')
        )
            ->leftJoin('fee_challans', 'fee_challans.id', '=', 'fee_challan_details.fee_challan_id')
            ->where('fee_challan_details.fees_type_id', 4)
            ->where('fee_challans.status', 2)
            ->whereIn('fee_challans.id', $fee_challan_ids)
            ->groupBy('fee_challan_details.student_id')
            ->having(DB::raw('COUNT(fee_challan_details.student_id)'), '>', '8')
            ->with([
                'student' => function ($query) {
                    // Select specific columns from the "students" table
                    $query->select(
                        'id',
                        'name',
                        'father_name',
                        'mobile_no',
                        'admission_id',
                        'student_class_id',
                        'global_section_id'
                    );
                },
                'student.studentClass',
                'student.globalSection',
                'student.studentLiableFees',
                'student.feeChallanDetailsLast'
            ])
            ->get();

        return $this->sendResponse($sessionWise_students, '', 200);
    }




    public function custom_fee(Request $req)
    {
        $dts = $req->start;
        $dts1 = $req->end;
        $hostel_fees = FeeChallanDetail::select('fee_challan_details.student_id', DB::raw('count(fee_challan_details.student_id) as months'), DB::raw('sum(fee_challan_details.amount) as total'))
            ->leftJoin('fee_challans', 'fee_challans.id', '=', 'fee_challan_details.fee_challan_id')
            ->where('fee_challan_details.fees_type_id', 4)->where('fee_challans.status', 2)->whereBetween('fee_challan_details.fee_month', [$dts, $dts1])->groupBy('fee_challan_details.student_id')
            ->having(DB::raw('COUNT(fee_challan_details.student_id)'), '>', '6')
            ->get();
        $hostel_fees->load('student.studentClass', 'student.globalSection', 'student.studentLiableFees', 'student.feeChallanDetailsLast');
        //  $hostel_fees= DB::table('fee_challans')
        //            ->leftJoin('fee_challan_details', 'fee_challans.id', '=', 'fee_challan_details.fee_challan_id')// joining the contacts table , where user_id and contact_user_id are same
        //        ->select('fee_challans.student_id', DB::raw('SUM(fee_challan_details.amount) as total'), 'fee_challan_details.fee_month','fee_challan_details.amount')
        //     ->where('fee_challan_details.fees_type_id','=','4')
        //    ->groupBy('fee_challan_details.student_id')
        //   ->get();
        return $hostel_fees;
    }
    public function studentcount(Request $req)
    {
        $from = date($req->start);
        $to = date($req->end);
        return DB::table('students')->select(DB::raw('count(id) as id', 'campus_id', 'student_class_id', 'global_section_id'))->whereBetween('Joining_date', [$from, $to])->groupBy('campus_id')->get();
        //return Student::select('count(id)','campus_id','student_class_id','global_section_id')->whereBetween('Joining_date', [$from, $to])->get();
        //return Student::whereBetween('Joining_date', [$from, $to])->get();
    }
    public function allhostelstudents(Request $req)
    {
        $hostel_fees = StudentLiableFee::where('fees_type_id', 7)->get();
        $hostel_fees->load('student.studentClass', 'student.globalSection', 'student.session');
        $total_fee = $hostel_fees->sum('amount');

        $data = [
            'total_fee_amount' => $total_fee,
            'hostel_fees' => StudentLiableFeeResource::collection($hostel_fees),
        ];

        return $this->sendResponse($data, []);
    }
    public function allCampus()
    {
        $allCampus = Campus::get();
        return $allCampus;
    }
    public function allClasses(Request $req)
    {
        $idx = $req->id;
        $classes = CampusClass::with('student')->where('campus_id', $idx)->get();
        //$classes->load('student');
        return $classes;
        // $section_name=DB::table('campus_classes')
        //->select('student_classes.name','student_classes.id')
        //->join('student_classes','campus_classes.student_class_id','=','student_classes.id')
        //->where('campus_classes.campus_id','=',$req->campid)
        //->get();
        //return $section_name;
    }
    public function allClasses1(Request $req)
    {
        $classes = StudentClass::get();
        //$classes->load('student');
        return $classes;
        // $section_name=DB::table('campus_classes')
        //->select('student_classes.name','student_classes.id')
        //->join('student_classes','campus_classes.student_class_id','=','student_classes.id')
        //->where('campus_classes.campus_id','=',$req->campid)
        //->get();
        //return $section_name;
    }
    public function allsession(Request $req)
    {
        return Session::get();
        // $section_name=DB::table('campus_classes')
        //->select('student_classes.name','student_classes.id')
        //->join('student_classes','campus_classes.student_class_id','=','student_classes.id')
        //->where('campus_classes.campus_id','=',$req->campid)
        //->get();
        //return $section_name;
    }

    public function classSec(Request $req)
    {
        return ClassSection::with('student')->with('section')->where('campus_id', '=', $req->campid)->where('student_class_id', '=', $req->classid)->get();
        //   $sect=DB::table('class_sections')
        //->select('global_sections.id','global_sections.name','student_classes.id','student_classes.name','class_sections.campus_id')
        //->join('global_sections','class_sections.global_section_id','=','global_sections.id')
        //->join('student_classes','class_sections.student_class_id','=','student_classes.id')
        //->where('class_sections.campus_id',$req->campid)->where('class_sections.student_class_id',$req->classid)
        //->get();
        //$genric=ClassSection::where('campus_id',$req->campid)->where('student_class_id',$req->sci)->get();
        echo $sect;
        return $sect;
    }
    public function challans(Request $req)
    {
        // $allstd=Student::select(
        //function($query) {
        //$query->select('name')->from('campuses')->where('id',campus_id)->get();
        //}
        //)
        //->where('campus_id',$req->campid)->where('student_class_id',$req->classid)->where('global_section_id',$req->gsid)->get();
        // return $allstd;
        $v1 = $req->sectionid;
        $v2 = $req->classid;
        $hostel_fees =  FeeChallan::with('student')->with('campus')->where('status', '0')->where('campus_id', '2')->get();
        $hostel_fees->load('student.studentClass', 'student.globalSection', 'student.session');
        return $hostel_fees;
        //return $chaln->('global_section_id','14')->get();
    }
    public function challandetails(Request $req)
    {
        return FeeChallanDetail::where('fee_challan_id', '=', $req->chid)->get();
    }
    public function updateChallan(Request $req)
    {
        //FeeChallan::where('id',$req->idd)->update(['payable'=>$req->pay]);

        FeeChallanDetail::where('id', $req->idd)->update(['amount' => $req->pay]);
        $values = 0;


        $sims = FeeChallanDetail::where('fee_challan_id', $req->idc)->selectRaw('sum(amount) as total')->get();
        foreach ($sims as $sm) {
            $values = $sm['total'];
        }
        FeeChallan::where('id', $req->idc)->update(['payable' => $values]);

        return $values;
    }
    public function bankers(Request $req)
    {
        return BankAccountCategory::all();
    }
    public function bankType(Request $req)
    {
        return BankAccount::where('bank_account_category_id', $req->actype)->get();
    }
    public function chalangeric(Request $req)
    {
        FeeChallanDetail::where('id', $req->idd)->update(['amount' => $req->pay]);
        $res = FeeChallanDetail::where('id', $req->idd)->get();

        foreach ($res as $sg) {
            $chlns = $sg['fee_challan_id'];
        }
        echo $chlns;
        $delValues = 0;
        $result = FeeChallanDetail::where('fee_challan_id', '=', $chlns)->selectRaw('ifnull(sum(amount),0) as total')->get();
        foreach ($result as $sm) {
            $delValues = $sm['total'];
        }
        return FeeChallan::where('id', $chlns)->update(['payable' => $delValues, 'status' => '0']);
    }

    public function genrateDV(Request $req)
    {
        $rests = $req->best;
        $vax = $req->stas;
        $values = 0;

        if ($vax == 'checked') {
            $chlns = FeeChallan::select('*')->orderBy('challan_no', 'desc')->limit(1)->first();
            $chlnsid = FeeChallan::select('*')->orderBy('id', 'desc')->limit(1)->first();
            $chlns1 = $chlns['challan_no'] + 1;
            $chlns1id = $chlnsid['id'] + 1;
            $chnn = FeeChallan::find($req->idd);
            $chnn = $chnn->replicate();
            $chnn->id = $chlns1id;
            $chnn->challan_no = $chlns1; // the new project_id
            $chnn->save();
            $chlnsx1 = $chnn['challan_no'];
            $sep_tag = explode(',', $rests);
            foreach ($sep_tag as $a) {
                FeeChallanDetail::where('id', $a)->update(['fee_challan_id' => $chlns1id]);
            }
            $delValues = 0;
            $result = FeeChallanDetail::where('fee_challan_id', '=', $req->idd)->selectRaw('ifnull(sum(amount),0) as total')->get();
            foreach ($result as $sm) {
                $delValues = $sm['total'];
            }
            if ($delValues == '0') {
                $id = FeeChallan::where('id', $req->idd);
                $id->forceDelete();
            }
            $sims = FeeChallanDetail::where('fee_challan_id', $chlns1id)->selectRaw('sum(amount) as total')->get();
            foreach ($sims as $sm) {
                $values = $sm['total'];
            }
            FeeChallan::where('challan_no', $chlns1)->update(['payable' => $values, 'bank_account_id' => $req->bank, 'status' => '1', 'received_date' => $req->dates, 'paid' => $values]);
            $simsx = FeeChallanDetail::where('fee_challan_id', $req->idd)->selectRaw('sum(amount) as total')->get();
            $upvalues = 0;
            foreach ($simsx as $sm) {
                $upvalues = $sm['total'];
            }
            return FeeChallan::where('id', $req->idd)->update(['payable' => $upvalues, 'status' => '0']);
        } else {
            $values = 0;
            //$date=date('Y-m-d H:i:s');

            $sims = FeeChallanDetail::where('fee_challan_id', $req->idd)->selectRaw('sum(amount) as total')->get();
            foreach ($sims as $sm) {
                $values = $sm['total'];
            }
            FeeChallan::where('id', $req->idd)->update(['payable' => $values]);
            return FeeChallan::where('id', $req->idd)->update(['bank_account_id' => $req->bank, 'status' => '1', 'received_date' => $req->dates, 'paid' => $values]);
        }
    }
    public function challansrch(Request $req)
    {
        $dpx = 0;
        $ex = Student::where('admission_id', $req->id)->get();
        foreach ($ex as $dx) {
            $dpx = $dx['id'];
        }
        $sumtotal = FeeChallan::where('student_id', $dpx)->sum('payable');
        $hostel_fees =  FeeChallan::with('student')->with('feeChallanDetails')->with('campus')->where('status', '0')->where('student_id', $dpx)->get();
        $hostel_fees->load('student.studentClass', 'student.globalSection', 'student.session', 'campus.printAccountNos');
        return $hostel_fees;
        //return ['challan'=>$hostel_fees,'total'=>$sumtotal];
        //return $chaln->('global_section_id','14')->get();
    }
    public function challansrchinvoice(Request $req)
    {
        $sumtotal = FeeChallan::where('challan_no', $req->id)->sum('payable');
        $hostel_fees =  FeeChallan::with('student')->with('feeChallanDetails')->with('campus')->where('status', '0')->where('challan_no', $req->id)->get();
        $hostel_fees->load('student.studentClass', 'student.globalSection', 'student.session');
        return $hostel_fees;
        //return ['challan'=>$hostel_fees,'total'=>$sumtotal];
        //return $chaln->('global_section_id','14')->get();
    }
    public function challansrchreg(Request $req)
    {
        $sumtotal = FeeChallan::where('student_id', $req->id)->sum('payable');
        $hostel_fees =  FeeChallan::with('student')->with('feeChallanDetails')->with('campus')->where('status', '0')->where('student_id', $req->id)->get();
        $hostel_fees->load('student.studentClass', 'student.globalSection', 'student.session');
        return $hostel_fees;
        //return ['challan'=>$hostel_fees,'total'=>$sumtotal];
        //return $chaln->('global_section_id','14')->get();
    }
    public function challansrch1(Request $req)
    {
        $dpx = 0;
        $ex = Student::where('admission_id', $req->id)->get();
        foreach ($ex as $dx) {
            $dpx = $dx['id'];
        }
        $sumtotal = FeeChallan::where('student_id', $dpx)->sum('payable');
        $hostel_fees =  FeeChallan::with('student')->with('feeChallanDetails')->with('campus')->with('bank_account')->where('status', '1')->where('student_id', $dpx)->get();
        $hostel_fees->load('student.studentClass', 'student.globalSection', 'student.session');
        return $hostel_fees;
        //return ['challan'=>$hostel_fees,'total'=>$sumtotal];
        //return $chaln->('global_section_id','14')->get();
    }
    public function challansrch3(Request $req)
    {
        $dpx = 0;
        $ex = Student::where('admission_id', $req->id)->get();
        foreach ($ex as $dx) {
            $dpx = $dx['id'];
        }
        $hostel_fees =  FeeChallan::with('feeChallanDetails1')->where('status', '2')->where('student_id', $dpx)->orderBy('id', 'desc')->first();
        return $hostel_fees;
    }
    public function challansrch2(Request $req)
    {

        $sumtotal = FeeChallan::where('bank_account_id', $req->ac)->where('received_date', $req->dt)->sum('payable');
        $hostel_fees =  FeeChallan::with('student')->with('feeChallanDetails')->with('campus')->with('bank_account')->where('status', '1')->where('bank_account_id', $req->ac)->where('received_date', $req->dt)->orderBy('updated_at', 'ASC')->get();
        $hostel_fees->load('student.studentClass', 'student.globalSection', 'student.session');
        return $hostel_fees;
        //return ['challan'=>$hostel_fees,'total'=>$sumtotal];
        //return $chaln->('global_section_id','14')->get();
    }
    // public function updatefees(Request $req)
    // {
    //     //  $now = new DateTime();
    //     //$now->format('Y-m-d H:i:s');
    //     $now = Carbon::now();
    //     $banks = $req->bnk;
    //     $chln = $req->chalan;
    //     $datez = $req->date;
    //     $sep_tag = explode(',', $chln);
    //     foreach ($sep_tag as $a) {
    //         FeeChallan::where('id', $a)->update(['bank_account_id' => $banks, 'status' => '1', 'feed_at' => $now, 'received_date' => $datez, 'paid' => DB::raw('payable')]);
    //         $chln = FeeChallan::where('id', $a)->pluck('student_id');
    //         echo $chln;
    //         $stid = Student::where('id', $chln)->get();
    //         echo $stid;
    //         foreach ($stid as $b) {
    //             if ($b['status'] == 3) {
    //                 $student = Student::orderBy('admission_id', 'desc')->pluck('admission_id')->first();
    //                 $as = "1";
    //                 echo $student;
    //                 Student::where('id', $chln)->update(['status' => '2', 'admission_id' => $student + $as]);
    //                 HighestValue::where('id', '1')->update(['admission_id' => $student + $as]);
    //                 //HighestValue
    //             }
    //         }
    //     }
    // }



    public function feerolebacks(Request $req)
    {
        $hostel_fees =  FeeChallan::with('student')->with('feeChallanDetails')->with('campus')->where('status', '1')->get();
        $hostel_fees->load('student.studentClass', 'student.globalSection', 'student.session');
        return $hostel_fees;
    }
    public function rolebackfinal(Request $req)
    {
        $ids = $req->chid;
        $sep_tag = explode(',', $ids);
        foreach ($sep_tag as $a) {
            FeeChallan::where('id', $a)->update(['status' => '0', 'paid' => '0', 'bank_account_id' => '0', 'received_date' => null]);
        }
    }
}
