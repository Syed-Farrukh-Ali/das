<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BankAccountResource;
use App\Models\BankAccount;
use App\Models\BankAccountCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BankAccountController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bankAccount = BankAccount::all();

        return $this->sendResponse(BankAccountResource::collection($bankAccount), []);
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
        $validator = Validator::make($request->all(), [
            'bank_account_category_id' => ['required', Rule::in(BankAccountCategory::pluck('id')->toArray())],
            'sub_account_id' => 'required|exists:sub_accounts,id',
            'bank_name' => 'required|string|max:50',
            'bank_branch' => 'required|string|max:100',
            'account_title' => 'required|string|max:100',
            'account_number' => 'required|string|max:100',
            'account_head' => 'required|string|max:100',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 442);
        }

        DB::beginTransaction();
        try {
            BankAccount::create($request->only([
                'bank_account_category_id',
                'sub_account_id',
                'bank_name',
                'bank_branch',
                'account_title',
                'account_number',
                'account_head',
            ]));
        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->sendError('internal server error', [], 500);
        }
            DB::commit();

        return $this->sendResponse([], 'bank account created successfully', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function show(BankAccount $bankAccount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function edit(BankAccount $bankAccount)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:50',
            'bank_branch' => 'required|string|max:100',
            'account_title' => 'required|string|max:100',
            'account_number' => 'required|string|max:100',
            'account_head' => 'required|string|max:100',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 442);
        }

        DB::beginTransaction();
        try {
            $bankAccount->update($request->only([
                'bank_name',
                'bank_branch',
                'account_title',
                'account_number',
                'account_head',
            ]));
        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->sendError('internal server error', [], 500);
        }
            DB::commit();

        return $this->sendResponse([], 'bank account updated successfully', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function destroy(BankAccount $bankAccount)
    {
        return $this->sendError('not allowed it may contain challans', [], 420);
        $deleted = $bankAccount->delete();
        if ($deleted) {
            return $this->sendResponse([], 'bank account successfully removed', 200);
        }
    }
}
