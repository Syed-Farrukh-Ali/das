<?php

namespace App\Http\Controllers\Api;

use App\Models\HeadOffice;
use App\Repository\HeadOfficeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HeadOfficeController extends BaseController
{
    public function __construct(HeadOfficeRepository $headOfficeRepository)
    {
        $this->headOfficeRepository = $headOfficeRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse($this->headOfficeRepository->index(), []);
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
        $validator = $this->validateHeadOffice($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->headOfficeRepository->store($request), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(HeadOffice $headoffice)
    {
        return $this->sendResponse($this->headOfficeRepository->show($headoffice), []);
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
    public function update(Request $request, HeadOffice $headoffice)
    {
        $validator = $this->validateHeadOffice($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->headOfficeRepository->update($request, $headoffice), []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(HeadOffice $headoffice)
    {
        return $this->sendResponse($this->headOfficeRepository->destroy($headoffice), []);
    }

    private function validateHeadOffice(Request $request)
    {
        return Validator::make($request->all(), [
            'email' => $request->route()->getName() == 'headoffice.store' ? 'unique:users|required' : '',
            // 'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string',
            'province' => 'required|string|max:255',
            'longitude' => 'required|string|',
            'latitude' => 'required|string|',

        ]);
    }
}
