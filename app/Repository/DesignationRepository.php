<?php

namespace App\Repository;

use App\Http\Resources\DesignationResource;
use App\Models\Designation;
use App\Repository\Interfaces\DesignationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DesignationRepository extends BaseRepository implements DesignationRepositoryInterface
{
    /**$userRepository
     * ProfileRepository constructor.
     *
     * @param User $model
     */
    public function __construct(Designation $model)
    {
        parent::__construct($model);
    }

    public function index()
    {
        return DesignationResource::collection(Designation::all());
    }

    public function show(Designation $designation)
    {
        return new DesignationResource($designation);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $designation = Designation::create([
                'name' => $request->name,
            ]);
        } catch (\Throwable $e) {
            dd($e);
            DB::rollBack();

            return false;
        }
        DB::commit();

        return new DesignationResource($designation);
    }

    public function update(Request $request, Designation $designation)
    {
        DB::beginTransaction();
        try {
            $designation->update([
                'name' => $request->name,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }
        DB::commit();

        return new DesignationResource($designation);
    }

    public function destroy(Designation $designation)
    {
        $designation->delete();

        return response()->json('designation successfully deleted');
    }
}
