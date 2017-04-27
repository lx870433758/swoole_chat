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
        
        return response()->json(['status' => '100']);
    }

    public function get_user_info(Request $request){
        $fd = $request->input('fd');
        $redis = Redis::connection();
        $user = $redis->get('user:'.$fd);
        if($user){
            return response()->json(['status' => '100','data' => $user]);
        }else{
            return response()->json(['status' => '101','data' => ""]);
        }

    }
}
