<?php
namespace App\Handler;
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/4/18
 * Time: 20:35
 */
use Illuminate\Support\Facades\Redis;
use App\Model\Users;
class Swoole extends \swoole_websocket_server{

    public function  swoole_start(){

        $ws = new \swoole_websocket_server($this->host, $this->port);
        $ws->on('open', function ($ws, $request) {
            $GLOBALS['fd'][] = $request->fd;

            //绑定用户
            $data = json_encode(['type' => 'login' ,'data' => ['fd' =>$request->fd]]);
            $ws->push($request->fd,$data);

            //所有用户更新用户列表
            $add_user = json_encode(['type' => 'add_user' ,'data' => ['fd' =>$request->fd]]);
            foreach($GLOBALS['fd'] as $i){
                $ws->push($i,$add_user);
            }
        });

        $ws->on('message', function ($ws, $frame) {
            $redis = Redis::connection();
            $userInfo = $redis->get('user:'.$frame->fd);
            $userInfo = json_decode($userInfo);
            $msg =  json_encode(['type'=>'msg', 'data' =>['fd' =>$frame->fd,'msg' =>$frame->data,'avatar' => $userInfo->avatar,'user_name' => $userInfo->user_name]]);
            foreach($GLOBALS['fd'] as $i){
                $ws->push($i,$msg);
            }
        });

        $ws->on('close', function ($ws, $fd) {
            $redis = Redis::connection();
            $userInfo = json_decode($redis->get('user:'.$fd));
            $user_list = $redis->exists('user_list') ? json_decode($redis->get('user_list'),true): [];
            unset($user_list[$userInfo->id]);
            echo $userInfo->id;
            $redis->set('user_list',json_encode($user_list));
        });

        $ws->start();
    }
}