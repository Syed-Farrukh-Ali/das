<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BankAccountCategoryResource;
use App\Models\BankAccountCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BankAccountCategoryController extends BaseController
{
 
    public function index()
    {
        return $this->sendResponse(BankAccountCategoryResource::collection(BankAccountCategory::with('bank_accounts')->get()), [], 200);
    }

   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:3|max:100',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }
        DB::beginTransaction();
        try {
            BankAccountCategory::create([
                'title' => $request->title,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->sendError('internal server error', [], 500);
        }
        DB::commit();

        return $this->sendResponse([], 'bank account category created successfully', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BankAccountCategory  $bankAccountCategory
     * @return \Illuminate\Http\Response
     */
    public function show(BankAccountCategory $bankAccountCategory)
    {
        $bankAccountCategory->load('bank_accounts');

        return $this->sendResponse(new BankAccountCategoryResource($bankAccountCategory), [], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BankAccountCategory  $bankAccountCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(BankAccountCategory $bankAccountCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BankAccountCategory  $bankAccountCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BankAccountCategory $bankAccountCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BankAccountCategory  $bankAccountCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(BankAccountCategory $bankAccountCategory)
    {
        if ($bankAccountCategory->bank_accounts->isEmpty()) {
            $deleted = $bankAccountCategory->delete();
            if ($deleted) {
                return $this->sendResponse([], 'bank account category successfully removed', 200);
            }
        }

        return $this->sendError('can not be delete, it has active bank accounts', [], 442);
    }
}
