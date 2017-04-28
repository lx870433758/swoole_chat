<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Users;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{

    public function index(Request $request){

        $redis = Redis::connection();
        $user_list = $redis->exists('user_list') ? json_decode($redis->get('user_list')): [];
        $id = $request->user()->id;
        dump($user_list);
        return;
        if($user_list->$id){
            unset($user_list->$id);
        }
        return view('Client.index',['request' => $request,'user_list' => $user_list]);
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
