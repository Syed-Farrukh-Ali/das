<?php

namespace App\Http\Controllers\Api;

use App\Models\RegistrationCard;
use App\Repository\RegistrationCardRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegistrationCardController extends BaseController
{
    public function __construct(RegistrationCardRepository $registrationCardRepository)
    {
        $this->registrationCardRepository = $registrationCardRepository;
    }

    /**staffMemberRepository
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse($this->registrationCardRepository->index(), []);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function issueCards($campus_id)
    {
        return $this->sendResponse($this->registrationCardRepository->issueCards($campus_id), []);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function notIssueCards($campus_id)
    {
        return $this->sendResponse($this->registrationCardRepository->notIssueCards($campus_id), []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validateRegistrarionCards($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->registrationCardRepository->store($request), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(RegistrationCard $registrationcard)
    {
        return $this->sendResponse($this->registrationCardRepository->show($registrationcard), []);
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
    public function update(Request $request, RegistrationCard $registrationcard)
    {
        $validator = $this->validateRegistrarionCards($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->registrationCardRepository->update($request, $registrationcard), []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(RegistrationCard $registrationcard)
    {
        return $this->sendResponse($this->registrationCardRepository->destroy($registrationcard), []);
    }

    private function validateRegistrarionCards(Request $request)
    {
        return Validator::make($request->all(), [
            'issue_at' => 'required|date',
            'test_date' => 'required|date',
            'test_time' => 'required|string',
            'interview_date' => 'required|date',
            'status' => 'required|string',
        ]);
    }
}
