<?php

namespace App\Repository;

use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Repository\Interfaces\BranchRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchRepository extends BaseRepository implements BranchRepositoryInterface
{
    /**$userRepository
     * ProfileRepository constructor.
     *
     * @param User $model
     */
    public function __construct(Branch $model)
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
        return BranchResource::collection(Branch::all());
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
            $branch = Branch::create([
                'bank_id' => $request->bank_id,
                'branch_name' => $request->branch_name,
                'branch_code' => $request->branch_code,
                'address' => $request->address,
            ]);
        } catch (\Throwable $e) {
            dd($e);
            DB::rollBack();

            return false;
        }
        DB::commit();

        return new BranchResource($branch);
    }

    public function show(Branch $branch)
    {
        return new BranchResource($branch);
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function update(Request $request, Branch $branch)
    {
        DB::beginTransaction();
        try {
            $branch->update([
                'branch_name' => $request->branch_name,
                'branch_code' => $request->branch_code,
                'address' => $request->address,

            ]);
        } catch (\Throwable $e) {
            dd($e);
            DB::rollBack();

            return false;
        }
        DB::commit();

        return new BranchResource($branch);
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();

        return response()->json('branch successfully deleted');
    }
}
