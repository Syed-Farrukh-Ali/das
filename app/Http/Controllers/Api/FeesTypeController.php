<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Api\BaseController;
use App\Models\FeesType;
use App\Repository\FeesTypeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeesTypeController extends BaseController
{
    public function __construct(FeesTypeRepository $feesTypeRepository)
    {
        $this->feesTypeRepository = $feesTypeRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse($this->feesTypeRepository->index(), []);
    }

    public function show(FeesType $feestype)
    {
        return $this->sendResponse($this->feesTypeRepository->show($feestype), []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:fees_types',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->feesTypeRepository->store($request), []);
    }
}
