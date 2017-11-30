<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<div id="msg"></div>
<input type="text" id="text">
<input type="submit" value="发送数据" onclick="song()">
</body>
<script>
    var msg = document.getElementById("msg");
    var wsServer = 'ws://106.14.10.215:9505';
    var websocket = new WebSocket(wsServer);
    //onopen监听连接打开
    websocket.onopen = function (evt) {
        msg.innerHTML = websocket.readyState;
    };

    function song(){
        var text = document.getElementById('text').value;
        document.getElementById('text').value = '';
        //向服务器发送数据
        websocket.send(text);
    }

    //onmessage 监听服务器数据推送
    websocket.onmessage = function (evt) {
        msg.innerHTML += evt.data +'<br>';
        console.log('Retrieved data from server: ' + evt);
    };

</script>
</html>