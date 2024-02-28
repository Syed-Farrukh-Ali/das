<?php

namespace App\Http\Controllers\Api;

use App\Models\Hostel;
use App\Repository\HostelRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HostelController extends BaseController
{
    public function __construct(HostelRepository $hostelRepository)
    {
        $this->hostelRepository = $hostelRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse($this->hostelRepository->index(), []);
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
        $validator = $this->validateHostel($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->hostelRepository->store($request), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Hostel $hostel)
    {
        return $this->sendResponse($this->hostelRepository->show($hostel), []);
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
    public function update(Request $request, Hostel $hostel)
    {
        $validator = $this->validateHostel($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->hostelRepository->update($request, $hostel), []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Hostel $hostel)
    {
        return $this->sendResponse($this->hostelRepository->destroy($hostel), []);
    }

    private function validateHostel(Request $request)
    {
        return Validator::make($request->all(), [
            'address_1' => 'required|string|max:255',
            'address_2' => 'required|string|max:255',
            'longitude' => 'required|string',
            'latitude' => 'required|string',

        ]);
    }
}
