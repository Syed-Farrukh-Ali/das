<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\FeeChallanDetailResource;
use App\Http\Resources\FeeChallanResource;
use App\Http\Resources\FeeChallanResourceCopy;
use App\Http\Resources\StudentResource;
use App\Models\Campus;
use App\Models\Certificate;
use App\Models\FeeChallan;
use App\Models\FeeChallanDetail;
use App\Models\Student;
use App\Repository\FeesChallanRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FeesChallanVoucherController extends BaseController
{
    public function __construct(FeesChallanRepository $feesChallanRepository)
    {
        $this->feesChallanRepository = $feesChallanRepository;
    }
     public function serial_id(Request $req){
        $cid=Certificate::orderBy('id','desc')->pluck('id')->first();
            return $cid+1;
    }

    public function showAllChallans() // unpaid
    {
        // code...
        $challans = FeeChallan::select([DB::raw("SUM(payable) as netpay,bank_account_id")])->where('status', 1)->groupBy('bank_account_id')->get();
        $challans_for_total = FeeChallan::where('status', 1)->get();
        $challans->load('bank_account');
        $totalPayable = $challans_for_total->sum('payable');
        $data = [
            'total_payable' => $totalPayable,
            'challan'=>$challans,
            

        ];

        return $data;
    }
    public function add_certificate(Request $req){
        $ex=Student::where('admission_id',$req->code)->pluck('id');
        Student::where('admission_id',$req->code)->update(['status'=>'6','struck_off_date'=>$req->leaving_date]);
       return Certificate::create([
                 'student_id' => $ex[0],
                 'issue_date' => $req->issue_date,
                 'leaving_date' => $req->leaving_date,
                 'class_passed_fail' => $req->passed_class,
                 'total_marks' => $req->total_Marks,
                 'obtain_marks' => $req->obtain_marks,
                 'class_position' => $req->class_position,
                 'total_Attendance' => $req->total_att,
                 'attendance' => $req->att,
                 'migration_to' => $req->migration,
                 'activity' => 'NULL',
                 'certificate_type_id' =>'1'
             ]);     
    }
    public function add_certificate1(Request $req){
        $ex=Student::where('admission_id',$req->code)->pluck('id');
        Student::where('admission_id',$req->code)->update(['status'=>'7','struck_off_date'=>$req->leaving_date]);
       return Certificate::create([
                 'student_id' => $ex[0],
                 'issue_date' => $req->issue_date,
                 'leaving_date' => $req->leaving_date,
                 'class_passed_fail' => $req->passed_class,
                 'total_marks' => $req->total_Marks,
                 'obtain_marks' => $req->obtain_marks,
                 'class_position' => $req->class_position,
                 'total_Attendance' => $req->total_att,
                 'attendance' => $req->att,
                 'activity' => $req->migration,
                 'migration_to' => 'NULL',
                 'certificate_type_id' =>'2'
             ]);     
    }
    public function viewCerificate(Request $req){
        //$ex[0]
        //$ex=Student::where('admission_id',$req->code)->pluck('id');
         //return Student::where('student_id',$ex[0])->with('certificate')->with('certificate_type')->get();
         //return Certificate::where('student_id',$ex[0])->with('student')->with('certificate_type')->get();
         $challans = Student::where('admission_id',$req->search_keyword)->with('certificate')->with('campus')->with('globalSection')->with('studentClass')->get();
        //$challans->load('certificate');
        return $challans;
        
    }

}
