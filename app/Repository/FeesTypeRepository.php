<?php

namespace App\Repository;

use App\Http\Resources\FeesTypeResource;
use App\Models\FeesType;
use App\Repository\Interfaces\FeesTypeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeesTypeRepository extends BaseRepository implements FeesTypeRepositoryInterface
{
    /**$userRepository
     * ProfileRepository constructor.
     *
     * @param User $model
     */
    public function __construct(FeesType $model)
    {
        parent::__construct($model);
    }

    public function index()
    {
        return FeesTypeResource::collection(FeesType::all());
    }

    public function show(FeesType $feestype)
    {
        return new FeesTypeResource($feestype);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            FeesType::create([
                'name' => $request->name,
            ]);
            // create student registration id
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }
        DB::commit();

        return true;
    }
}
