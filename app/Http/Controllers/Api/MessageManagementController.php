<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\SMS\CreateUnitRequest;
use App\Http\Requests\SMS\UpdateHeadOfficeMessagesRequest;
use App\Http\Requests\SMS\UpdateUnitMessagesRequest;
use App\Models\HeadOfficeMessages;
use App\Models\ManageUnitMessages;
use App\Models\UnitMessages;
use App\Traits\ResponseMethodTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MessageManagementController extends Controller
{
    use ResponseMethodTrait;
    public function index() {
        // $response = Http::post('http://example.com/users', [
        //     'name' => 'Steve',
        //     'role' => 'Network Administrator',
        // ]);
        $response = Http::get('http://sgdcity-api.dassmis.com/public/api/get-settings');

        if ($response->successful()) {
            // API call was successful
            $settings = $response->json(); 
            return $settings;
        } else {
            // API call failed
            $statusCode = $response->status();
            return $statusCode;
        }
    }

    public function update_head_office_messages(UpdateHeadOfficeMessagesRequest $request) {
        if (HeadOfficeMessages::where('id', '=', 1)->updateOrCreate(['id' => 1], ['assign_sms' => $request->assign_sms, 'remaining_sms' => $request->assign_sms])) {
            return $this->sendResponse([], 'Head office messages updated successfully.', 200);
        } else {
            return $this->sendError('Head office messages not updated successfully.', [], 400);
        }
    }
    public function get_head_office_messages() {
        if ($result = HeadOfficeMessages::find(1)->toArray()) {
            return $this->sendResponse($result, 'Record available.', 200);
        } else {
            return $this->sendError('No record available.', [], 400);
        }
    }

    public function create_new_unit(CreateUnitRequest $request) {
        $head_office_data = HeadOfficeMessages::find(1);
        $current_messages = $head_office_data->remaining_sms - $request->assign_sms;
        if ($current_messages < 0) {
            return $this->sendError('Available messages in head office are less then you are assigning to '.$request->unit_name.' unit.', [], 400);
        } else {
            try { 
                DB::beginTransaction();
                $response = App::call('App\Http\Controllers\Api\MessageManagementController@update_unit_messages', ['assign_sms' => $request->assign_sms]);
                if ($response == true) {
                    // $response = Http::post('http://127.0.0.1:8000/api/update-unit-messages' , [
                    //     'assign_sms' => $request->assign_sms,
                    // ]);

                    // dd($response);
                    // if ($response->successful()) {
                    //     // API call was successful
                    //     $settings = $response->json(); 
                    //     return $settings;
                    // } else {
                    //     // API call failed
                    //     $statusCode = $response->status();
                    //     return $statusCode;
                    // }
                    if (HeadOfficeMessages::where('id', '=', 1)->update(['remaining_sms' => $current_messages])) {
                        if (ManageUnitMessages::create($request->all())) {
                            DB::commit();
                            return $this->sendResponse([], $request->assign_sms.' messages are assigned to '.$request->unit_name.' unit successfully.', 200);
                        } else {
                            DB::rollback();
                            return $this->sendError($request->assign_sms.' messages are not assigned to '.$request->unit_name.' unit.', [], 400);
                        }
                    } else {
                        DB::rollback();
                        return $this->sendError('Unit Messages not updated successfully.', [], 400);
                    }
                } else {
                    DB::rollback();
                    return $this->sendError($request->assign_sms.' messages are not assigned to '.$request->unit_name.' unit.', [], 400);
                }
            }  catch (\Exception $e) {
                DB::rollBack();
                return $this->sendError($request->assign_sms.' messages are not assigned to '.$request->unit_name.' unit.', [], 400);
            }
        }
    }
    
    public function update_unit_messages(UpdateUnitMessagesRequest $request) {
        $details = UnitMessages::find(1);
        if (UnitMessages::where('id', '=', 1)->updateOrCreate(['id' => 1], ['assign_sms' => $details->assign_sms + $request->assign_sms, 'remaining_sms' => $details->remaining_sms + $request->assign_sms])) {
            return $success = true;
        } else {
            return $success = false;
        }
    }

    public function get_unit_messages() {
        if ($result = UnitMessages::find(1)->toArray()) {
            return $this->sendResponse($result, 'Record available.', 200);
        } else {
            return $this->sendError('No record available.', [], 400);
        }
    }
}
