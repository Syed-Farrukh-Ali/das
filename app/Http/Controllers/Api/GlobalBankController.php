<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\GlobalBankResource;
use App\Models\GlobalBank;
use Illuminate\Http\Request;

class GlobalBankController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $banks = GlobalBankResource::collection(GlobalBank::all());

        return $this->sendResponse($banks, []);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GlobalBank  $globalBank
     * @return \Illuminate\Http\Response
     */
    public function show(GlobalBank $bank)
    {
        $bank = new GlobalBankResource($bank);

        return $this->sendResponse($bank, []);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GlobalBank  $globalBank
     * @return \Illuminate\Http\Response
     */
    public function edit(GlobalBank $globalBank)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GlobalBank  $globalBank
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GlobalBank $globalBank)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GlobalBank  $globalBank
     * @return \Illuminate\Http\Response
     */
    public function destroy(GlobalBank $globalBank)
    {
        //
    }
}
