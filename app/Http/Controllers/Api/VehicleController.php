<?php

namespace App\Http\Controllers\Api;

use App\Models\Vehicle;
use App\Repository\VehicleRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehicleController extends BaseController
{
    public function __construct(VehicleRepository $vehicleRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
    }

    public function index()
    {
        return $this->sendResponse($this->vehicleRepository->index(), []);

        // return UserResource::collection(User::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validateVehicle($request);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->vehicleRepository->store($request), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Campus  $campus
     * @return \Illuminate\Http\Response
     */
    public function show(Vehicle $vehicle)
    {
        return $this->sendResponse($this->vehicleRepository->show($vehicle), []);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Campus  $campus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validator = $this->validateVehicle($request);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->vehicleRepository->update($request, $vehicle), []);
    }

    public function destroy(Vehicle $vehicle)
    {
        return $this->sendResponse($this->vehicleRepository->destroy($vehicle), []);
    }

    public function validateVehicle($request)
    {
        return Validator::make($request->all(), [
            'rp_number' => 'required',
            'model' => 'required|string',
            'seats' => 'required',
        ]);
    }
}
