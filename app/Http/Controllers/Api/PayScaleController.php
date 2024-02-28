<?php

namespace App\Http\Controllers\api;

use App\Models\PayScale;
use App\Repository\PayScaleRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class PayScaleController extends BaseController
{
    public function __construct(PayScaleRepository $payScaleRepository)
    {
        $this->payScaleRepository = $payScaleRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse($this->payScaleRepository->index(), []);
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
        $validator = $this->validatePayScale($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->payScaleRepository->store($request), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PayScale  $payScale
     * @return \Illuminate\Http\Response
     */
    public function show(PayScale $payscale)
    {
        return $this->sendResponse($this->payScaleRepository->show($payscale), []);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PayScale  $payScale
     * @return \Illuminate\Http\Response
     */
    public function edit(PayScale $payScale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PayScale  $payScale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PayScale $payscale)
    {
        $validator = $this->validatePayScale($request);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->payScaleRepository->update($request, $payscale), []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PayScale  $payScale
     * @return \Illuminate\Http\Response
     */
    public function destroy(PayScale $payscale)
    {
        return $this->sendError('delete option is disabled by developer, will enable on request', [], 422);

        return $this->sendResponse($this->payScaleRepository->destroy($payscale), []);
    }

    private function validatePayScale(Request $request)
    {
        return Validator::make($request->all(), [
            'payscale' => 'required|integer',
            'basic' => 'required|integer',
            'increment' => 'required|integer',
            'maximum' => 'required|integer',
            'gp_fund' => 'required|integer',
            'welfare_fund' => 'required|integer',

        ]);
    }
}
