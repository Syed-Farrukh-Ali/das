<?php

namespace App\Http\Controllers\Api;

use App\Models\Bank;
use App\Repository\BankRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankController extends BaseController
{
    public function __construct(BankRepository $bankRepository)
    {
        $this->bankRepository = $bankRepository;
    }

  
    public function index()
    {
        return $this->sendResponse($this->bankRepository->index(), []);
    }

    
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
        $validator = $this->validateBank($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->bankRepository->store($request), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bank  $bank
     * @return \Illuminate\Http\Response
     */
    public function show(Bank $bank)
    {
        return $this->sendResponse($this->bankRepository->show($bank), []);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Bank  $bank
     * @return \Illuminate\Http\Response
     */
    public function edit(Bank $bank)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bank  $bank
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bank $bank)
    {
        $validator = $this->validateBank($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->bankRepository->update($request, $bank), []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bank  $bank
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bank $bank)
    {
        return $this->sendResponse($this->bankRepository->destroy($bank), []);
    }

    private function validateBank(Request $request)
    {
        return Validator::make($request->all(), [

            'name' => 'required|string|max:255',
            'account_title' => 'required|string',
            'account_no' => 'required|string',

        ]);
    }
}
