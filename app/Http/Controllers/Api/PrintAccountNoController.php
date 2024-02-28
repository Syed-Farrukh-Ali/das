<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\UpdatePrintAccountNoRequest;
use App\Http\Resources\CampusResourceSimple;
use App\Http\Resources\PrintAccountNoResource;
use App\Models\Campus;
use App\Models\PrintAccountNo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrintAccountNoController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $campuses = Campus::with('printAccountNos')->get();

        $data = [
            'campuses' => CampusResourceSimple::collection($campuses),
        ];

        return $this->sendResponse($data, 'all campus print account numbers', 200);
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
     * @param  \App\Http\Requests\StorePrintAccountNoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campus_id' => 'required|exists:campuses,id',
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:100',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), []);
        }
        $printAccountNo = PrintAccountNo::create([
            'campus_id' => $request->campus_id,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
        ]);
        $data = [
            'print_account_no' => new PrintAccountNoResource($printAccountNo->load('campus')),
        ];

        return $this->sendResponse($data, 'account number for print stored', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PrintAccountNo  $printAccountNo
     * @return \Illuminate\Http\Response
     */
    public function show(PrintAccountNo $printAccountNo)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PrintAccountNo  $printAccountNo
     * @return \Illuminate\Http\Response
     */
    public function edit(PrintAccountNo $printAccountNo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePrintAccountNoRequest  $request
     * @param  \App\Models\PrintAccountNo  $printAccountNo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePrintAccountNoRequest $request, PrintAccountNo $printAccountNo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PrintAccountNo  $printAccountNo
     * @return \Illuminate\Http\Response
     */
    public function destroy(PrintAccountNo $printAccountNo)
    {
        $printAccountNo->delete();

        return $this->sendResponse(true, 'deleted successfully', 200);
    }
}
