<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Users;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
class ClientController extends Controller
{

    public function index(Request $request){
        return view('Client.index',['request' => $request]);
    }
    public function user_bind(Request $request){
        $fd = $request->input('fd');
        $redis = Redis::connection();
        $redis->set('user:'.$fd,$request->user());
        if(!$redis->exists('user:'.$fd)){
            return response()->json(['status' => '101']);
        }
        return response()->json(['status' => '100']);
    }
}
