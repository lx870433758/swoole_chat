<?php
namespace App\Handler;
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/4/18
 * Time: 20:35
 */

class Swoole{
    private  $host;
    private  $port;
    public function swoole_start(){
        $ws = new swoole_websocket_server($this->host, $this->port);

        $ws->on('open', function ($ws, $request) {
            $fd[] = $request->fd;
            $GLOBALS['fd'][] = $fd;
        });

        $ws->on('message', function ($ws, $frame) {
            $msg =  "用户$frame->fd :$frame->data\n";
            foreach($GLOBALS['fd'] as $aa){
                foreach($aa as $i){
                    $ws->push($i,$msg);
                }
            }
        });

        $ws->on('close', function ($ws, $request) {
            echo "client-{$fd} is closed\n";
        });

        $ws->start();
    }
    public function open($ws,$request){

    }
    public function message($ws,$frame){

    }
    public function close($ws,$request){

    }
}