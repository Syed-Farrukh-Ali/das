<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\StaffMemberResource;
use App\Models\Campus;
use App\Models\StaffMember;
use App\Models\User;
use App\Repository\StaffMemberRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StaffMemberController extends BaseController
{
    public function __construct(StaffMemberRepository $staffMemberRepository)
    {
        $this->staffMemberRepository = $staffMemberRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse($this->staffMemberRepository->index(), []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validateStaffMember($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->staffMemberRepository->store($request), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(StaffMember $staffmember)
    {
        return $this->sendResponse($this->staffMemberRepository->show($staffmember), []);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StaffMember $staffmember)
    {
        $validator = $this->validateStaffMember($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        if (_campusAccess($request->campus_id)) {
            return $this->sendResponse($this->staffMemberRepository->update($request, $staffmember), []);
        } else {
            return $this->sendError([], 'please select your own campus');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(StaffMember $staffmember)
    {
        return $this->sendResponse($this->staffMemberRepository->destroy($staffmember), []);
    }

    private function validateStaffMember(Request $request)
    {
        return Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',

            'applied_for' => 'nullable|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'cnic_no' => 'nullable|integer',
            'qualification' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|max:255',
            'dob' => 'nullable|date|date_format:Y-m-d',
            'remarks' => 'nullable|string|max:255',
            'mobile_no' => 'nullable',
            'phone' => 'nullable|integer',
            'address' => 'nullable|string|max:255',
        ]);
    }

    public function campusWiseStaff($campus_id)
    {
        $campus = Campus::find($campus_id);
        return $this->sendResponse(StaffMemberResource::collection($campus->staff_members), []);
    }

    public function updateStaffMember(Request $request, $user_id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255|unique:users',
            'password' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $user = User::find($user_id);

        if ($user->campus_id) {
            if (_campusAccess($user->campus_id)) {
                if (StaffMember::where('user_id', $user_id)) {
                    $user->update($request->all());

                    if ($request->has('password')) {
                        $user->update(['password' => Hash::make($request->password)]);
                    }
                } else {
                    return $this->sendError([], 'Sorry! this user is not a Staff Member');
                }
            } else {
                return $this->sendError([], 'please select your own campus');
            }
        } else {
            return $this->sendError([], 'This user does not have campus');
        }

        return $this->sendResponse([], 'User Updated successfully');
    }
}
