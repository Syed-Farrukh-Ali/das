<?php

namespace App\Http\Controllers\Api;

use App\Models\Concession;
use App\Repository\ConcessionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConcessionController extends BaseController
{
    public function __construct(ConcessionRepository $concessionRepository)
    {
        $this->concessionRepository = $concessionRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse($this->concessionRepository->index(), []);
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
        $validator = $this->validateConcession($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->concessionRepository->store($request), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Concession $concession)
    {
        return $this->sendResponse($this->concessionRepository->show($concession), []);
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
    public function update(Request $request, Concession $concession)
    {
        $validator = Validator::make($request->all(), [
            'percentage' => 'nullable|integer|max:100|min:0',
            'amount' => 'max:20000|required_without:percentage',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->concessionRepository->update($request, $concession), []);
    }

    public function destroy(Concession $concession)
    {
        $removed = $concession->update(['is_used' => false]);
        if ($removed) {
            return $this->sendResponse([], 'concession successfully removed', []);
        }

        return $this->sendError(['internal server error'], [], 500);
    }

    private function validateConcession(Request $request)
    {
        return Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'percentage' => 'nullable|integer|max:100|min:0',
            'amount' => 'max:20000|required_without:percentage',

        ]);
    }
}
