<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;
use App\Repository\BranchRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BranchController extends BaseController
{
    public function __construct(BranchRepository $branchRepository)
    {
        $this->branchRepository = $branchRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse($this->branchRepository->index(), []);
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
        $validator = $this->validateBranch($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->branchRepository->store($request), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function show(Branch $branch)
    {
        return $this->sendResponse($this->branchRepository->show($branch), []);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function edit(branch $branch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Branch $branch)
    {
        $validator = $this->validateBranch($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->branchRepository->update($request, $branch), []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function destroy(Branch $branch)
    {
        return $this->sendResponse($this->branchRepository->destroy($branch), []);
    }

    private function validateBranch(Request $request)
    {
        return Validator::make($request->all(), [
            'branch_name' => 'required|string|max:255',
            'branch_code' => 'required|string',
            'address' => 'required|string|max:255',
        ]);
    }
}
