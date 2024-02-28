<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingController extends BaseController
{
    public function getSetting()
    {
        $setting = Setting::where('id', 1)->get()->first();

        $data = [
            "settings" => new SettingResource($setting),
        ];

        return $this->sendResponse($data, []);
    }

    public function updateSettings(Request $request)
    {
        // return $this->sendResponse($request->logo_file, []);

        $validator = Validator::make($request->all(), [
            'late_fee_fine' => 'required',
            'unit_name' => 'nullable',
            'gp_fund_years' => 'required|integer',
            'alphanumeric_adm_no' => 'required|boolean',
            'director_sign' => 'required|boolean',
            'send_message' => 'required|boolean',
            'sms_api_login' => 'required',
            'sms_api_password' => 'required',
            'director_number' => 'required',
            // 'logo_file' => 'required|file',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), [], 422);
        }

        $unitName = $request->unit_name;
        $directorNumber = $request->director_number;


        // return $this->sendResponse($request->director_number, []);


        if (!$unitName) {
            $unitName = '';
        }

        if (!$directorNumber) {
            $directorNumber = '';
        }

        $logopath = NULL;

        if ($request->logo_file) {
            $logopath = Storage::disk('logo')->put('', $request->logo_file);
        } else {
            $logopath = NULL;
        }

        $startLogoPath = NULL;

        if ($request->start_logo_file) {
            $startLogoPath = Storage::disk('logo')->put('', $request->start_logo_file);
        } else {
            $startLogoPath = NULL;
        }

        $updates = [
            'late_fee_fine' => $request->late_fee_fine,
            'unit_name' => $unitName,
            'gp_fund_years' => $request->gp_fund_years,
            'director_number' => $directorNumber,
            'alphanumeric_adm_no' => $request->alphanumeric_adm_no,
            'director_sign' => $request->director_sign,
            'send_message' => $request->send_message,
            'sms_api_login' => $request->sms_api_login,
            'sms_api_password' => $request->sms_api_password,
        ];

        // return $this->sendResponse($updates, []);


        // Check if $logopath is not null before adding it to the updates array
        if ($logopath !== NULL) {
            $updates['logo_file'] = $logopath;
        }

        if ($startLogoPath !== NULL) {
            $updates['start_logo_file'] = $startLogoPath;
        }

        // return $this->sendResponse($updates, []);

        Setting::where('id', 1)->update($updates);


        return $this->sendResponse("Settings Successfully Updated", []);
    }
}
