<?php

namespace App\Repository;

use App\Http\Resources\HeadOfficeResource;
use App\Models\HeadOffice;
use App\Models\User;
use App\Repository\Interfaces\HeadOfficeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HeadOfficeRepository extends BaseRepository implements HeadOfficeRepositoryInterface
{
    /**$userRepository
     * ProfileRepository constructor.
     *
     * @param User $model
     */
    public function __construct(HeadOffice $model)
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
        return HeadOfficeResource::collection(HeadOffice::all());
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
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $user->assignRole('Head Office');

            $headoffice = $user->head_office()->create([
                'user_id' => $user->id,
                'title' => $request->title,
                'address' => $request->address,
                'city' => $request->city,
                'province' => $request->province,
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
            ]);
        } catch (\Throwable$e) {
            dd($e);
            DB::rollBack();

            return false;
        }
        DB::commit();

        return new HeadOfficeResource($headoffice);
    }

    public function show(HeadOffice $headoffice)
    {
        return new HeadOfficeResource($headoffice);
    }

    /**
     * @param  illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Throwable
     */
    public function update(Request $request, HeadOffice $headoffice)
    {
        DB::beginTransaction();
        try {
            $headoffice->update([
                'title' => $request->title,
                'address' => $request->address,
                'city' => $request->city,
                'province' => $request->province,
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,

            ]);

            $headoffice->user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
        } catch (\Throwable$e) {
            dd($e);
            DB::rollBack();

            return false;
        }
        DB::commit();

        return new HeadOfficeResource($headoffice);
    }

    public function destroy(HeadOffice $headoffice)
    {
        $headoffice->user()->delete();
        $headoffice->delete();

        return response()->json('headoffice successfully deleted');
    }
}
