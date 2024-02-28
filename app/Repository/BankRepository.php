<?php

namespace App\Repository;

use App\Http\Resources\BankResource;
use App\Models\Bank;
use App\Repository\Interfaces\BankRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankRepository extends BaseRepository implements BankRepositoryInterface
{
    /**$userRepository
     * ProfileRepository constructor.
     *
     * @param User $model
     */
    public function __construct(Bank $model)
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
        return BankResource::collection(Bank::all());
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
            $bank = Bank::create([
                'head_office_id' => auth()->user()->head_office->id,
                'name' => $request->name,
                'account_title' => $request->account_title,
                'account_no' => $request->account_no,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }
        DB::commit();

        return new BankResource($bank);
    }

    public function show(Bank $bank)
    {
        return new BankResource($bank);
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function update(Request $request, Bank $bank)
    {
        DB::beginTransaction();
        try {
            $bank->update([
                'name' => $request->name,
                'account_title' => $request->account_title,
                'account_no' => $request->account_no,

            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }
        DB::commit();

        return new BankResource($bank);
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();

        return response()->json('bank successfully deleted');
    }
}
