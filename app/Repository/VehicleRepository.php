<?php

namespace App\Repository;

use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Repository\Interfaces\VehicleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleRepository extends BaseRepository implements VehicleRepositoryInterface
{
    /**
     * ProfileRepository constructor.
     *
     * @param  User  $model
     */
    public function __construct(Vehicle $model)
    {
        parent::__construct($model);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return VehicleResource::collection(Vehicle::all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Vehicle::create([
                'campus_id' => $request->campus_id,
                'rp_number' => $request->rp_number,
                'model' => $request->model,
                'seats' => $request->seats,
            ]);
        } catch (\Throwable $e) {
            dd($e);
            DB::rollBack();

            return false;
        }
        DB::commit();

        return new VehicleResource($user);
    }

    public function show(Vehicle $vehicle)
    {
        return new VehicleResource($vehicle);
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        DB::beginTransaction();
        try {
            $vehicle->update([
                'campus_id' => auth()->user()->campus->id,
                'rp_number' => $request->rp_number,
                'model' => $request->model,
                'seats' => $request->seats,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }

        DB::commit();

        return new VehicleResource($vehicle);
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return response()->json(' vehicle successfully deleted');
    }
}
