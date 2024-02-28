<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Accounts\AccountChartResource;
use App\Models\AccountChart;
use Illuminate\Http\Request;

class AccountChartController extends BaseController
{
   
    public function index()
    {
        $AccountChart = AccountChart::all();

        return $this->sendResponse(AccountChartResource::collection($AccountChart), [], 200);
    }

    

}
