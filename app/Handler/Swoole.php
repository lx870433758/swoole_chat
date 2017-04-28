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
            $redis = Redis::connection();
            //更新fd列表
            $fd_list = $redis->exists('fd_list') ? json_decode($redis->get('fd_list'),true): [];
            $fd_list[]=$request->fd;
            $redis->set('fd_list',json_encode($fd_list));
            echo "$request->fd 加入fd列表成功";

            //绑定用户
            $getInfo =  $request->get;
            $id = $getInfo['id'];
            $userInfo = Users::find($id);
            $redis->set('user:'.$request->fd,$userInfo);
            echo "$request->fd 绑定用户成功";
            //更新用户列表
            $user_list = $redis->exists('user_list') ? json_decode($redis->get('user_list'),true): [];
            $user_list[$id] = $userInfo;
            $redis->set('user_list', json_encode($user_list)) ;

            //添加用户到所有用户列表
            $userInfo->fd = $request->fd;
            $add_user = json_encode(['type' => 'add_user' ,'data' => $userInfo]);
            foreach($fd_list as $i){
                //if($i != $request->fd){
                    $ws->push($i,$add_user);
                //}

            }
            echo "$request->fd 添加用户到所有用户列表\n";
        });

        $ws->on('message', function ($ws, $frame) {
            $redis = Redis::connection();
            $userInfo = $redis->get('user:'.$frame->fd);
            $userInfo = json_decode($userInfo);
            $msg =  json_encode(['type'=>'msg', 'data' =>['fd' =>$frame->fd,'msg' =>$frame->data,'avatar' => $userInfo->avatar,'user_name' => $userInfo->user_name,'id' => $userInfo->id,'time' => date('H:i:s',time())]]);
            $fd_list = $redis->exists('fd_list') ? json_decode($redis->get('fd_list'),true): [];
            foreach($fd_list as $i){
                $ws->push($i,$msg);
            }
        });

        $ws->on('close', function ($ws, $fd) {
            $redis = Redis::connection();
            $uesrinfo = $redis->get('user:'.$fd);
            $uesrinfo = json_decode($uesrinfo);

            //// 用户列表删除用户
            $user_list = json_decode($redis->get('user_list'),true);
            unset($user_list[$uesrinfo->id]);
            if(isset($user_list[$uesrinfo->id])){
                echo "删除失败";
            }else{
                echo "删除成功";
            }
            $redis->set('user_list', json_encode($user_list)) ;
            echo "$uesrinfo->id 用户列表删除用户";

            //删除fd列表
            $fd_list = $redis->exists('fd_list') ? json_decode($redis->get('fd_list'),true): [];
            $key=array_search($fd ,$fd_list);
            unset($fd_list[$key]);
            $redis->set('fd_list',json_encode($fd_list));
            echo  "删除fd列表 $fd 客户端";
            //推送
            $del_user = json_encode(['type' => 'del_user' ,'data' => $uesrinfo]);
            $fd_list = $redis->exists('fd_list') ? json_decode($redis->get('fd_list'),true): [];
            foreach($fd_list as $i){
                if($fd != $i){
                    $ws->push($i,$del_user);
                }

            }
            echo "$fd 退出成功\n";
        });

        $ws->start();
    }
}