<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\StudentLiableFeeResource;
use App\Models\Student;
use App\Models\StudentLiableFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentLiableFeeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'fees_type_id' => 'required|integer|exists:fees_types,id',
            'amount' => 'required|integer|max:20000|min:100',
            'concession_amount' => 'nullable|integer',
            'remarks' => 'nullable|string|max:125',

        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $studentLiablefee = Student::find($request->student_id)->studentLiableFees()->create([
            'fees_type_id' => $request->fees_type_id,
            'amount' => $request->amount,
            'concession_amount' => $request->concession_amount,
            'remarks' => $request->remarks,
        ]);

        return $this->sendResponse(new StudentLiableFeeResource($studentLiablefee), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StudentLiableFee  $studentLiableFee
     * @return \Illuminate\Http\Response
     */
    public function show(StudentLiableFee $studentLiableFee)
    {
        return $this->sendResponse(new StudentLiableFeeResource($studentLiableFee), []);
    }

   public function studentFees(Student $student)
   {
       $liablefee = $student->studentLiableFees;

       return $this->sendResponse(StudentLiableFeeResource::collection($liablefee->load('feesType')), []);
   }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StudentLiableFee  $studentLiableFee
     * @return \Illuminate\Http\Response
     */
    public function edit(StudentLiableFee $studentLiableFee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StudentLiableFee  $studentLiableFee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StudentLiableFee $studentLiableFee)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer',
            'concession_amount' => 'nullable|integer',
            'remakrs' => 'nullable|string|max:125',
            // 'status'     =>  ['nullable', Rule::in(['monthly','yearly'])],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $studentLiableFee->update([
            'amount' => $request->amount,
            'concession_amount' => $request->concession_amount,
            'remarks' => $request->remarks,

        ]);

        return $this->sendResponse(new StudentLiableFeeResource($studentLiableFee), []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StudentLiableFee  $studentLiableFee
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudentLiableFee $studentLiableFee)
    {
        $deleted = $studentLiableFee->delete();
        if ($deleted) {
            return $this->sendResponse('fee deleted successfully', []);
        }

        return $this->sendError('oops ! some thing went wrong', [], 500);
    }
}