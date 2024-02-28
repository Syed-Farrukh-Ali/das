<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Accounts\VoucherTypeResource;
use App\Models\VoucherType;
use Illuminate\Http\Request;

class VoucherTypeController extends BaseController
{
   
    public function index()
    {
        return $this->sendResponse(VoucherTypeResource::collection(voucherType::all()), [], 200);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VoucherType  $voucherType
     * @return \Illuminate\Http\Response
     */
 
    public function show(VoucherType $voucherType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VoucherType  $voucherType
     * @return \Illuminate\Http\Response
     */
  
    public function edit(VoucherType $voucherType)
    {
        //
    }

 
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VoucherType  $voucherType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VoucherType $voucherType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VoucherType  $voucherType
     * @return \Illuminate\Http\Response
     */
    public function destroy(VoucherType $voucherType)
    {
        //
    }
}
