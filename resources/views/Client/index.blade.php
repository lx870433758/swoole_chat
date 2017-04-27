<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>聊天室 - editor:yinq</title>
    <link rel="shortcut icon" href="favicon.png">
    <link rel="icon" href="favicon.png" type="image/x-icon">
    <link type="text/css" rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
</head>

<body>
<div class="chatbox">
    <div class="chat_top fn-clear">
        <div class="logo"><img src="{{ asset('images/logo.png')}}" width="190" height="60" alt=""/></div>
        <div class="uinfo fn-clear">
            <div class="uface"><img src="{{ env('IMG_URL') }}/{{ $request->user()->avatar }}" width="40" height="40" alt=""/></div>
            <div class="uname">
                {{ $request->user()->user_name }}<i class="fontico down"></i>
                <ul class="managerbox">
                    <li><a href="#"><i class="fontico lock"></i>修改密码</a></li>
                    <li><a href="/auth/logout"><i class="fontico logout"></i>退出登录</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="chat_message fn-clear">
        <div class="chat_left">
            <div class="message_box" id="message_box">

                {{--<div class="msg_item fn-clear">
                  <div class="uface"><img src="{{ asset('images/hetu.jpg')}}" width="40" height="40"  alt=""/></div>
                  <div class="item_right">
                    <div class="msg own">那个统计表也不能说明一切</div>
                    <div class="name_time">河图 · 30秒前</div>
                  </div>
                </div>--}}
            </div>
            <div class="write_box">
                <textarea id="message" name="message" class="write_area" placeholder="说点啥吧..."></textarea>
                <input type="hidden" name="fromname" id="fromname" value="河图"/>
                <input type="hidden" name="to_uid" id="to_uid" value="0">
                <div class="facebox fn-clear">
                    <div class="expression"></div>
                    <div class="chat_type" id="chat_type">群聊</div>
                    <button name="" class="sub_but">提 交</button>
                </div>
            </div>
        </div>
        <div class="chat_right">
            <ul class="user_list" title="双击用户私聊">
                <li class="fn-clear selected"><em>所有用户</em></li>
            </ul>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function (e) {
        var msg = document.getElementById("message_box");
        var user_list = $('.user_list');
        var wsServer = 'ws://106.14.10.215:9505?id={{ $request->user()->id }}';
        var websocket = new WebSocket(wsServer);
        var img_qian = "{{ env('IMG_URL') }}"+"/";
        var user_html = '<li class="fn-clear" data-id="{{ $request->user()->id }}"><span><img src="'+ img_qian +'{{ $request->user()->avatar }}" width="30" height="30"  alt=""/></span><em>{{ $request->user()->user_name }}</em><small class="online" title="在线"></small></li>';
        var info;
        //onopen监听连接打开
        websocket.onopen = function (evt) {
            if (websocket.readyState == 1) {
                msg.innerHTML = "正在连接聊天室";
                //user_list.append(user_html);
            } else {
                msg.innerHTML = "聊天室连接失败";
            }

        };
        //onmessage 监听服务器数据推送
        websocket.onmessage = function (evt) {
            info = JSON.parse(evt.data);
            switch (info.type) {
                case 'login':
                    userBind(info.data.fd,msg,user_html);
                    break;
                case 'msg':
                    sendMessage(event, info.data.user_name, to_uid, to_uname, info.data.msg, img_qian + info.data.avatar);
                    break;
                default:
                    console.log(evt.fd);
            }
            //
            //console.log(evt.fd);
        };
        $('#message_box').scrollTop($("#message_box")[0].scrollHeight + 20);
        $('.uname').hover(
                function () {
                    $('.managerbox').stop(true, true).slideDown(100);
                },
                function () {
                    $('.managerbox').stop(true, true).slideUp(100);
                }
        );
        var to_uid = 0; // 默认为0,表示发送给所有用户
        var to_uname = '';
        $('.user_list > li').dblclick(function () {
            to_uname = $(this).find('em').text();
            to_uid = $(this).attr('data-id');
            if (to_uname == fromname) {
                alert('您不能和自己聊天!');
                return false;
            }
            if (to_uname == '所有用户') {
                $("#toname").val('');
                $('#chat_type').text('群聊');
            } else {
                $("#toname").val(to_uid);
                $('#chat_type').text('您正和 ' + to_uname + ' 聊天');
            }
            $(this).addClass('selected').siblings().removeClass('selected');
            $('#message').focus().attr("placeholder", "您对" + to_uname + "说：");
        });

        $('.sub_but').click(function (event) {
            websocket.send($("#message").val());
            //sendMessage(event, "用户:"+msg.fd, to_uid, to_uname,msg);
        });

        /*按下按钮或键盘按键*/
        $("#message").keydown(function(event){
         var e = window.event || event;
         var k = e.keyCode || e.which || e.charCode;
         //按下ctrl+enter发送消息
         if((event.ctrlKey && (k == 13 || k == 10) )){
             websocket.send($("#message").val());
         //sendMessage(event, fromname, to_uid, to_uname);
         }
         });
    });

    function sendMessage(event, from_name, to_uid, to_uname, msg, avatar) {
        if (to_uname != '') {
            msg = '您对 ' + to_uname + ' 说： ' + msg;
        }
        var htmlData = '<div class="msg_item fn-clear">'
                + '   <div class="uface"><img src="'  +avatar + '" width="40" height="40"  alt=""/></div>'
                + '   <div class="item_right">'
                + '     <div class="msg own">' + msg + '</div>'
                + '     <div class="name_time">' + from_name + ' · 30秒前</div>'
                + '   </div>'
                + '</div>';
        $("#message_box").append(htmlData);
        $('#message_box').scrollTop($("#message_box")[0].scrollHeight + 20);
        $("#message").val('');

    }
    function userBind(fd,msg,user_html) {
        $.post("/client/user_bind", {fd: fd},
                function (data) {
                    msg.innerHTML = "欢迎进入聊天室";
                    $('.user_list').append(user_html);

                });
    }
</script>
</body>
</html>
