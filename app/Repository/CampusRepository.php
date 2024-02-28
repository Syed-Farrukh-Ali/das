<?php

namespace App\Repository;

use App\Http\Resources\CampusResource;
use App\Http\Resources\CampusResourceSimple;
use App\Models\BankAccount;
use App\Models\Campus;
use App\Models\CampusClass;
use App\Models\ClassSection;
use App\Models\Session;
use App\Models\StudentClass;
use App\Models\User;
use App\Repository\Interfaces\CampusRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CampusRepository extends BaseRepository implements CampusRepositoryInterface
{
    /**
     * ProfileRepository constructor.
     *
     * @param  User  $model
     */
    public function __construct(Campus $model)
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
        $single_campus = auth()->user()->campus_id;
        if ($single_campus)
        {
            return CampusResourceSimple::collection(Campus::with('printAccountNos')->where('id',$single_campus)->get());

        }
        return CampusResource::collection(Campus::with('printAccountNos')->get());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $bank_accounts = BankAccount::whereIn('id',$request->bank_account_ids)->get();

//        DB::beginTransaction();
//        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $campus = $user->campus()->create([
                'head_office_id' => '1',
                'welfare_account_id' => $request->welfare_account_id,
                'type' => $request->type,
                'name' => $request->name,
                'code' => $request->code,
                'area' => $request->area,
                'city' => $request->city,
                'province' => $request->province,
                'contact' => $request->contact,
            ]);

            if ($bank_accounts->count() > 0){
                $campus->printAccountNos()->forceDelete();

                foreach ($bank_accounts as $bank_account){

                    $string_part = explode('-', $bank_account->bank_name);
                    $bank_name = $string_part[0];

                    $campus->printAccountNos()->create([
                        'bank_account_id' => $bank_account->id,
                        'bank_name' => $bank_name,
                        'account_number' => $bank_account->account_number,
                    ]);
                }
            }

            $user->update(['campus_id' => $campus->id]);

            $user->assignRole('Campus');
//        } catch (\Throwable $e) {
//            DB::rollBack();
//
//            return false;
//        }
//        DB::commit();

        // giving default values to a campus *********************************
        //***********************************************************************
        $session_id = Session::where('active_financial_year', 1)->first()->id;
        $plusValue = 1000;
        $class_ids = StudentClass::all()->pluck('id')->toArray();
        foreach ($class_ids as $key => $class_id) {
            CampusClass::create([
                'campus_id' => $campus->id,
                'student_class_id' => $class_id,

            ]);
            ClassSection::create([
                'campus_id' => $campus->id,
                'student_class_id' => $class_id,
                'global_section_id' => 1,
            ]);
            ClassSection::create([
                'campus_id' => $campus->id,
                'student_class_id' => $class_id,
                'global_section_id' => 2,
            ]);
            ClassSection::create([
                'campus_id' => $campus->id,
                'student_class_id' => $class_id,
                'global_section_id' => 3,
            ]);

            $campus->feestructures()->create([
                'student_class_id' => $class_id,
                'fee_type_id' => '1',
                'session_id' => $session_id,
                'amount' => 200,
            ]);
            $campus->feestructures()->create([
                'student_class_id' => $class_id,
                'fee_type_id' => '2',
                'session_id' => $session_id,
                'amount' => 1000,
            ]);
            $campus->feestructures()->create([
                'student_class_id' => $class_id,
                'fee_type_id' => '3',
                'session_id' => $session_id,
                'amount' => 3000,
            ]);
            $campus->feestructures()->create([
                'student_class_id' => $class_id,
                'fee_type_id' => '4',
                'session_id' => $session_id,
                'amount' => $plusValue + 300,
            ]);
            $plusValue = $plusValue + 300;

            $campus->feestructures()->create([
                'student_class_id' => $class_id,
                'fee_type_id' => '5',
                'session_id' => $session_id,
                'amount' => 99,
            ]);
            $campus->feestructures()->create([
                'student_class_id' => $class_id,
                'fee_type_id' => '6',
                'session_id' => $session_id,
                'amount' => 1000,
            ]);
            $campus->feestructures()->create([
                'student_class_id' => $class_id,
                'fee_type_id' => '7',
                'session_id' => $session_id,
                'amount' => 2000,
            ]);
            $campus->feestructures()->create([
                'student_class_id' => $class_id,
                'fee_type_id' => '9',
                'session_id' => $session_id,
                'amount' => 1000,
            ]);
            $campus->feestructures()->create([
                'student_class_id' => $class_id,
                'fee_type_id' => '11',
                'session_id' => $session_id,
                'amount' => 200,
            ]);
            $campus->feestructures()->create([
                'student_class_id' => $class_id,
                'fee_type_id' => '20',
                'session_id' => $session_id,
                'amount' => 3000,
            ]);
            $campus->feestructures()->create([
                'student_class_id' => $class_id,
                'fee_type_id' => '22',
                'session_id' => $session_id,
                'amount' => 5000,
            ]);
            $campus->feestructures()->create([
                'student_class_id' => $class_id,
                'fee_type_id' => '27',
                'session_id' => $session_id,
                'amount' => 20,
            ]);
            $campus->feestructures()->create([
                'student_class_id' => $class_id,
                'fee_type_id' => '28',
                'session_id' => $session_id,
                'amount' => 3800,
            ]);
        }

        //***********************************************************************

        return new CampusResource($campus);
    }

    public function show(Campus $campus)
    {
        return new CampusResource($campus);
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function update(Request $request, Campus $campus)
    {
        $bank_accounts = BankAccount::whereIn('id',$request->bank_account_ids)->get();

        DB::beginTransaction();
        try {
            $campus->update([
                'welfare_account_id' => $request->welfare_account_id,
                'name' => $request->name,
                'type' => $request->type,
                'code' => $request->code,
                'area' => $request->area,
                'city' => $request->city,
                'province' => $request->province,
                'contact' => $request->contact,
            ]);
            $campus->user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,

            ]);
            if ($request->password) {
                $campus->user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            if ($bank_accounts->count() > 0){
                $campus->printAccountNos()->forceDelete();

                foreach ($bank_accounts as $bank_account){

                    $string_part = explode('-', $bank_account->bank_name);
                    $bank_name = $string_part[0];

                    $campus->printAccountNos()->create([
                        'bank_account_id' => $bank_account->id,
                        'bank_name' => $bank_name,
                        'account_number' => $bank_account->account_number,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }

        DB::commit();

        return new CampusResource($campus);
    }

    public function destroy(Campus $campus)
    {
        $campus->user()->delete();
        $campus->delete();

        return response()->json('campus successfully deleted');
    }
}
