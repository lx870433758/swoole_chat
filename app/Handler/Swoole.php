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
            $getInfo =  $request->get;
            $id = $getInfo['id'];
            $userInfo = Users::find($id);

            $redis = Redis::connection();
            $redis->set('user:'.$request->fd,$userInfo);

            //更新用户列表
            $user_list = $redis->exists('user_list') ? json_decode($redis->get('user_list'),true): [];
            $user_list[$id] = $userInfo;
            $redis->set('user_list', json_encode($user_list)) ;

            //添加用户到所有用户列表
            $userInfo->fd = $request->fd;
            $add_user = json_encode(['type' => 'add_user' ,'data' => $userInfo]);
            foreach($GLOBALS['fd'] as $i){
                //if($i != $request->fd){
                    $ws->push($i,$add_user);
                //}

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
            $uesrinfo = $redis->get('user:'.$fd);
            $uesrinfo = json_decode($uesrinfo);

            //更新用户列表
            $user_list = json_decode($redis->get('user_list'),true);
            unset($user_list[$uesrinfo->id]);
            $redis->set('user_list', json_encode($user_list)) ;

            //删除用户到所有用户列表
            $del_user = json_encode(['type' => 'del_user' ,'data' => $uesrinfo]);
            foreach($GLOBALS['fd'] as $i){
                $ws->push($i,$del_user);
            }
        });

        $ws->start();
    }
}