<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/4/17
 * Time: 15:09
 */
//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new swoole_websocket_server("0.0.0.0", 9505);

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
    $fd[] = $request->fd;
    $GLOBALS['fd'][] = $fd;
    //$ws->push($request->fd, "hello, welcome\n");
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
    $msg =  "用户$frame->fd :$frame->data\n";
    $msg =  json_encode($frame);
//var_dump($GLOBALS['fd']);
//exit;
    foreach($GLOBALS['fd'] as $aa){
        foreach($aa as $i){
            $ws->push($i,$msg);
        }
    }
    // $ws->push($frame->fd, "server: {$frame->data}");
    // $ws->push($frame->fd, "server: {$frame->data}");
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();