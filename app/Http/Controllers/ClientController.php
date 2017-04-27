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
        $user_list = $redis->exists('user_list') ? json_decode($redis->get('user_list'),true): [];
        return view('Client.index',['request' => $request,'user_list' =>$user_list]);
    }
    public function user_bind(Request $request){
        $fd = $request->input('fd');
        $redis = Redis::connection();
        $redis->set('user:'.$fd,$request->user());

        $user_list = $redis->exists('user_list') ? json_decode($redis->get('user_list'),true): [];
        $user_list[$request->user()->id]=array(
            'id' => $request->user()->id,
            'user_name' => $request->user()->user_name,
            'avatar' => $request->user()->avatar,
            'email' => $request->user()->email,
            'phone' => $request->user()->phone,
            'name' => $request->user()->name,
        );
        $redis->set('user_list',json_encode($user_list));
        $redis->set('user:'.$fd,$request->user());
        if(!$redis->exists('user:'.$fd)){
            return response()->json(['status' => '101']);
        }
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
