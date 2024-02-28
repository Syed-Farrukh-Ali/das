<?php

namespace App\Repository;

use App\Http\Resources\StaffMemberResource;
use App\Models\Campus;
use App\Models\StaffMember;
use App\Models\User;
use App\Repository\Interfaces\StaffMemberRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffMemberRepository extends BaseRepository implements StaffMemberRepositoryInterface
{
    /**$userRepository
     * ProfileRepository constructor.
     *
     * @param User $model
     */
    public function __construct(StaffMember $model)
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
        // return new StaffMemberResource($staffMember);
        return StaffMemberResource::collection(StaffMember::all());
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
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'campus_id' => $request->campus_id,
                'password' => Hash::make($request->password),
            ]);
            $staffmember = $user->staff_member()->create([
                'campus_id' => $request->campus_id,
                'applied_for' => Campus::find($request->campus_id)->name,
                'full_name' => $request->full_name,
                'father_name' => $request->father_name,
                'nationality' => $request->nationality,
                'religion' => $request->religion,
                'experience' => $request->experience,
                'cnic_no' => $request->cnic_no,
                'qualification' => $request->qualification,
                'gender' => $request->gender,
                'marital_status' => $request->marital_status,
                'dob' => $request->dob,
                'remarks' => $request->remarks,
                'mobile_no' => $request->mobile_no,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
            $user->assignRole('Staff Member');
        } catch (\Throwable $e) {
            dd($e);
            DB::rollBack();

            return false;
        }
        DB::commit();

        return new StaffMemberResource($staffmember);
    }

    public function show(StaffMember $staffMember)
    {
        return new StaffMemberResource($staffMember);
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function update(Request $request, StaffMember $staffmember)
    {
        DB::beginTransaction();
        try {
            $staffmember->update([
                'applied_for' => $request->applied_for,
                'full_name' => $request->full_name,
                'father_name' => $request->father_name,
                'nationality' => $request->nationality,
                'religion' => $request->religion,
                'experience' => $request->experience,
                'cnic_no' => $request->cnic_no,
                'qualification' => $request->qualification,
                'gender' => $request->gender,
                'marital_status' => $request->marital_status,
                'dob' => $request->dob,
                'remarks' => $request->remarks,
                'mobile_no' => $request->mobile_no,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            $staffmember->user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }
        DB::commit();

        return new StaffMemberResource($staffmember);
    }

    public function destroy(StaffMember $staffmember)
    {
        $staffmember->user()->delete();
        $staffmember->delete();

        return response()->json('staffmember successfully deleted');
    }
}
