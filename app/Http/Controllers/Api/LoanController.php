<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\FeeChallanDetailResource;
use App\Http\Resources\FeeReturnResource;
use App\Http\Resources\LoanResource;
use App\Http\Resources\StudentResource;
use App\Models\Employee;
use App\Models\FeeChallanDetail;
use App\Models\FeeReturn;
use App\Models\Loan;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoanController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function salaryLoan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'sub_account_id' => 'required|exists:sub_accounts,id',
            'monthly_loan_installment' => 'required|integer|min:0',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }

        $employee = Employee::find($request->employee_id);

        $status = 0;
        if ($request->loan_taken > 0){
            $status = 1;
        }

        $loan = Loan::updateOrCreate([
            'employee_id' => $employee->id,
        ], [
            'sub_account_id' => $request->sub_account_id,
            'monthly_loan_installment' => $request->monthly_loan_installment,
            'loan_taken_date' => Carbon::now(),
//            'loan_taken' => $request->loan_taken,
            'status' => $status
        ]);

        $data = [
            'loan' => new LoanResource($loan->load('employee', 'subAccount')),
        ];

        return $this->sendResponse($data, [], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function employeeLoan(Request $request)
    {
        $validator = validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        $loan = Employee::find($request->employee_id)->loans()->first();

        if ($loan){
            $credit = $loan->subAccount
                ->general_ledgers
                ->sum('credit');

            $debit = $loan->subAccount
                ->general_ledgers
                ->sum('debit');

            $remaining_amount = $credit-$debit;
            $loan->remaining_amount = $remaining_amount;
        }

        $response = new LoanResource($loan->load('subAccount','employee'));

        return $this->sendResponse($response, [], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function feeReturnStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_admission_id' => 'required|exists:students,admission_id',
           // 'fees_type_id' => 'required|exists:fees_types,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
       // $fee_month = Carbon::today()->subMonth(10)->firstOfMonth();

        //$student = Student::where('admission_id', $request->student_admission_id)->get()->first();
        $student = Student::with('campus')->where('admission_id', $request->student_admission_id)->get()->first();
       // $fee_details = FeeChallanDetail::with('feeChallan')->where(['student_id' => $student->id, 'fees_type_id' => $request->fees_type_id])->where('fee_month', '>=', $fee_month)->get();
        $data = [
            'student' => StudentResource::make($student),
           // 'fee_details' => FeeChallanDetailResource::collection($fee_details),
            'fee_return_hiostory' => FeeReturnResource::collection($fee_returns),
        ];

        return $this->sendResponse($data, [], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function feeRetrun(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'fee_sub_account_id' => 'required|exists:sub_accounts,id',
            'fee_return_amount' => 'required|integer|min:1',
            'sub_account_id' => 'required|exists:sub_accounts,id',
            'remarks' => 'nullable',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        $feeReturn = FeeReturn::create([
            'student_id' => $request->student_id,
            'fee_sub_account_id' => $request->fee_sub_account_id,
            'fee_return_amount' => $request->fee_return_amount,
            'sub_account_id' => $request->sub_account_id,
            'remarks' => $request->remarks,
            'campus_id' => Student::find($request->student_id)->campus_id,
            'date' => Carbon::today(),
        ]);
        $feeReturn->load('student.studentClass', 'sub_account', 'voucher', 'feesType', 'fee_sub_account');
       // $data = [
         //   'fee_return_challans' => new FeeReturnResource($feeReturn),
    //    ];

       // return $this->sendResponse($data, 'successfully feereturned', 200);
              $response = new FeeReturnResource($feeReturn);

        return $this->sendResponse($response, 'Successfully fee returned');
    }

    public function feeReturnIndex()
    {
       // $feeReturns = FeeReturn::where('status', 1)->get();
         $feeReturns = FeeReturn::all();
        $feeReturns->load('student.studentClass', 'sub_account', 'voucher', 'feesType', 'fee_sub_account');
      //  $data = [
        //    'fee_return_challans' => FeeReturnResource::collection($feeReturns),
        //];

       // return $this->sendResponse($data, 'successfully feereturned', 200);
             $response = FeeReturnResource::collection($feeReturns);

        return $this->sendResponse($response,'');
    }
   public function studentFeeReturnHistory(Student $student)
    {
        $response = new StudentResource($student->load('fee_return.voucher'));

        return $this->sendResponse($response,'');
    }
    public function feeReturnEdit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fee_sub_account_id' => 'required|exists:sub_accounts,id',
            'fee_return_amount' => 'required|integer|min:1',
            'sub_account_id' => 'required',
            'remarks' => 'nullable',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        $feeReturnChallan = FeeReturn::find($request->fee_return_id);
        if ($feeReturnChallan->status == 2) {
            return $this->sendError('sorry this challan is entered into GL', []);
        }
        $feeReturnChallan->update([
            'fee_sub_account_id' => $request->fee_sub_account_id,
            'fee_return_amount' => $request->fee_return_amount,
            'sub_account_id' => $request->sub_account_id,
            'remarks' => $request->remarks,
        ]);
        $feeReturnChallan->load('student.studentClass', 'sub_account', 'voucher', 'feesType', 'fee_sub_account');
        $data = [
            'fee_return_challans' => new FeeReturnResource($feeReturnChallan),
        ];

        return $this->sendResponse($data, 'successfully feereturned', 200);
    }

    public function feeReturnDestroy(FeeReturn $feeReturn)
    {
        if ($feeReturn->status == 2) {
            return $this->sendError('sorry this challan is entered into GL', []);
        }
        $feeReturn->delete();

        return $this->sendResponse([], 'successfully deleted', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Loan $loan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Loan $loan)
    {
        //
    }
}
