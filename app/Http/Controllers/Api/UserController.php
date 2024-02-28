<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Repository\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        return $this->sendResponse($this->userRepository->index(), []);

        // return UserResource::collection(User::all());
    }

    public function store(Request $request)
    {
        $validator = $this->validateUserCampus($request);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->userRepository->store($request), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Campus  $campus
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $this->sendResponse($this->userRepository->show($user), []);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Campus  $campus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validator = $this->validateUserCampus($request);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        return $this->sendResponse($this->userRepository->update($request, $user), []);
    }

    public function destroy(User $user)
    {
        return $this->sendResponse($this->userRepository->destroy($user), []);
    }

   private function validateUserCampus(Request $request)
   {
       return Validator::make($request->all(), [
           'first_name' => 'required|string|max:255',
           'last_name' => 'required|string|max:255',
           'password' => 'string|min:6',
           'email' => $request->route()->getName() == 'user.store' ? 'unique:employees' : '',
           'email' => 'required|string|max:255',

           'name' => 'required|string|max:255',
           'code' => 'string|max:255',
           'area' => 'required|string|max:255',
           'city' => 'required|string|max:255',
           'province' => 'required|string|max:255',
           'contact' => 'string|max:255',
       ]);
   }
}
