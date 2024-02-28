<?php

namespace App\Repository;

use App\Http\Resources\RegistrationCardResource;
use App\Models\Campus;
use App\Models\RegistrationCard;
use App\Models\User;
use App\Repository\Interfaces\RegistrationCardRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RegistrationCardRepository extends BaseRepository implements RegistrationCardRepositoryInterface
{
    /**$userRepository
    * ProfileRepository constructor.
    *
    * @param User $model
    */
    public function __construct(RegistrationCard $model)
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
         // return RegistrationCardResource::collection(RegistrationCard::where('campus_id',auth()->user()));
     }

     public function issueCards($campus_id)
     {
         return RegistrationCardResource::collection(Campus::find($campus_id)->registrationCards()->with('student', 'campus')->where('status', 1)->latest()->get());
     }

     public function notIssueCards($campus_id)
     {
         return RegistrationCardResource::collection(Campus::find($campus_id)->registrationCards()->with('student.feeChallans.feeChallanDetails', 'campus')->where('status', 0)->latest()->get());
     }

     /**
      * Display the specified resource.
      *
      * @param  int  $id
      * @return \Illuminate\Http\Response
      */
     public function store(Request $request)
     {
         DB::beginTransaction();
         try {
             $registrationcard = RegistrationCard::create([
                 'student_id' => $request->student_id,
                 'campus_id' => $request->campus_id,
                 'issue_at' => $request->issue_at,
                 'test_date' => $request->test_date,
                 'test_time' => $request->test_time,
                 'interview_date' => $request->interview_date,
                 'status' => $request->status,
             ]);
         } catch (\Throwable $e) {
             dd($e);
             DB::rollBack();

             return false;
         }
         DB::commit();

         return new RegistrationCardResource($registrationcard->load('student'));
     }

     public function show(RegistrationCard $registrationcard)
     {
         return new RegistrationCardResource($registrationcard);
     }

     /**
      * @param  illuminate\Http\Request  $request
      * @return bool
      *
      * @throws \Throwable
      */
     public function update(Request $request, RegistrationCard $registrationcard)
     {
         DB::beginTransaction();
         try {
             $registrationcard->update([
                 'issue_at' => $request->issue_at,
                 'test_date' => $request->test_date,
                 'test_time' => $request->test_time,
                 'interview_date' => $request->interview_date,
                 'status' => $request->status,
             ]);
         } catch (\Throwable $e) {
             DB::rollBack();

             return false;
         }
         DB::commit();

         return new RegistrationCardResource($registrationcard->load('student'));
     }

     public function destroy(RegistrationCard $registrationcard)
     {
         $registrationcard->delete();

         return response()->json('staffmember successfully deleted');
     }
}
