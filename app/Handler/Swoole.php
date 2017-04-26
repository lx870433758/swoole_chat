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
            //$userId = $request->data->get->id;
            //$userInfo = Users::find($userId);
            //$userInfo->fd = $request->fd;
            //$redis = Redis::connection();
            //$redis->set("user:".$userId ,$userInfo);
            $ws->push($request->fd,$request->data->data);
        });

        $ws->on('message', function ($ws, $frame) {
            $msg =  json_encode(['fd' =>$frame->fd,'data' =>$frame->data,'avatar' => '','user_name' => '自定义']);
            foreach($GLOBALS['fd'] as $i){
                $ws->push($i,$msg);
            }
        });

        $ws->on('close', function ($ws, $request) {
            echo "client-{$fd} is closed\n";
        });

        $ws->start();
    }
}