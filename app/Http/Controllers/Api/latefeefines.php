<?php


namespace App\Http\Controllers\Api;
use App\Models\LateFeeFine;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class latefeefines extends Controller
{
    public function showfine(){
return LateFeeFine::all();
}

    public function updatefine(Request $req){
$fineAmount=$req->amount;
    //latefeefine::where('id','1')->update([['amount', $fineAmount]]);
LateFeeFine::where('id', '=', '1')->update(['amount' => $fineAmount]);
return LateFeeFine::all();
}
}
