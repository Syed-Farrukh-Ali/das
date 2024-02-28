<?php

namespace App\Http\Controllers\Api;

use App\Models\Campus;
use App\Models\SubAccount;
use App\Repository\CampusRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CampusController extends BaseController
{
    public function __construct(CampusRepository $campusRepository)
    {
        $this->campusRepository = $campusRepository;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */





    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse($this->campusRepository->index(), []);
    }



    public function store(Request $request)
    {
        $validator = $this->validateCampus($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->campusRepository->store($request), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Campus  $campus
     * @return \Illuminate\Http\Response
     */
    public function show(Campus $campus)
    {
        return $this->sendResponse($this->campusRepository->show($campus), []);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Campus  $campus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Campus $campus)
    {
        $validator = $this->validateCampus($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->campusRepository->update($request, $campus), []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Campus  $campus
     * @return \Illuminate\Http\Response
     */
    public function destroy(Campus $campus)
    {
        return $this->sendResponse($this->campusRepository->destroy($campus), []);
    }

    private function validateCampus(Request $request)
    {
        return Validator::make($request->all(), [

            'name' => 'required|string|max:100',
            'type' => 'required|string|max:100',
            'code' => 'nullable|string|max:3|min:3',
            'area' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'contact' => 'nullable|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:8|max:50',
            'email' => 'nullable|string|max:100',
            'bank_account_ids' => 'nullable|array',
            'bank_account_ids.*' => 'nullable|numeric|exists:bank_accounts,id',
            'welfare_account_id' => 'required|numeric|exists:sub_accounts,id',
        ]);
    }
}
