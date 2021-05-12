<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport"
          content="width=device-width, height=device-height, inital-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>转移通知</title>
    <style>
        body {
            font-family: -apple-system, SF UI Display, Arial, PingFang SC, Hiragino Sans GB, Microsoft YaHei, WenQuanYi Micro Hei, sans-serif;
            word-break: break-word !important;
            word-break: break-all;
        }

        p {
            font-size: 16px;
            font-weight: 400;
            line-height: 1.7;
            color: #2f2f2f;
            display: block;
            margin-block-start: 1em;
            margin-block-end: 1em;
            margin-inline-start: 0px;
            margin-inline-end: 0px;
        }

        .title {
            text-align: center;
            margin: 15px 0;
            font-size: 30px;
            font-weight: 700;
            line-height: 1.4;
            color: #2f2f2f;
        }

        h1 {
            display: block;
            font-size: 2em;
            margin-block-start: 0.67em;
            margin-block-end: 0.67em;
            margin-inline-start: 0px;
            margin-inline-end: 0px;
            font-weight: bold;
        }

        .download-cont,
        .email-cont {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .email-collect{
            height: 40px;
            line-height: 40px;
            width: 80%;
            border: none;
        }
        .email-collect input {
            padding: 8px;
            height: 24px;
            line-height: 24px;
            width: 100%;
            border: solid 2px #dc5b41;
            border-radius: 6px;
        }

        .download-btn {
            background-color: #dc5b41;
            text-align: center;
            height: 50px;
            width: 280px;
            line-height: 50px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 25px;
            box-shadow: 6px 6px 8px #dddddd;
            margin-top: 20px;
        }

        .download-btn:active {
            background-color: #af4934;
        }

        .download-btn a {
            display: inline-block;
            width: 100%;
            height: 50px;
            line-height: 50px;
            text-decoration: none;
            color: #fff;
        }
        .transfer-vip{
            color: #ffffff;
        }
        .email-input {
            height: 40px;
            line-height: 40px;
            width: 80%;
            border: none;
        }

        .email-input input {
            padding: 8px;
            height: 24px;
            line-height: 24px;
            width: 100%;
            border: solid 2px #dc5b41;
            border-radius: 6px;
        }
        .error-notice{
            display: none;
            color: red;
            font-size: 12px;
            height: 12px;
            line-height: 12px;
        }
        .useremail-error-notice{
            display: none;
            color: red;
            font-size: 12px;
            height: 12px;
            line-height: 12px;
        }
        .success-notice{
            display: none;
            color: green;
            font-size: 12px;
            height: 12px;
            line-height: 12px;
        }
        .waiting-notice{
            display: none;
            color: blue;
            font-size: 12px;
            height: 12px;
            line-height: 12px;
        }
    </style>
    <script type="text/javascript" src="{{ URL::asset('/js/jquery-3.3.1.min.js') }}"></script>
</head>
<body style="display: block;margin: 1em;">
<h1 class="title">转移通知</h1>
<div class="article"
     style="display: block;font-weight: 400;line-height: 1.8;margin-bottom: 70px;word-break: break-word;">
    <p>亲爱的用户您好：</p>
    <p>目前使用的See TestFlight地址失效，后续将无法继续更新，所有的用户转移到新APP。</p>
    <p>（注：之前你是什么会员，转移到新APP就是什么会员。）</p>
    <p><strong>如有问题联系邮箱：xunjie@protonmail.com</strong></p>
    <h4 style="font-size: 22px;margin: 20px 0 15px;">下面是转移操作步骤：</h4>
    <p><strong>1、下载新版See TestFlight版本</strong></p>
    <div class="download-cont">
        <div class="download-btn">
            <a class="download-link" href="https://testflight.apple.com/join/KoHLIzlw">下载 新版本See</a>
        </div>
    </div>
    <p><strong>2、转移操作：</strong></p>
    <p>你的See ID为：<strong>{{$uuid}}</strong>，转移后将获得 <strong>{{$time}}</strong> 天See VIP，谢谢支持。</p>
    <p><strong>请输入新版See邮箱账号（一定是邮箱哦！）：</strong></p>
    <div class="email-cont">
        <div class="email-input">
            <input type="text" name="see-uuid" id="see-uuid" value="" placeholder="请输入新版See邮箱账号"/>
        </div>
        <p class="error-notice">请输入新版See邮箱账号（新版See注册完成的邮箱账号）</p>
        <p class="success-notice"></p>
        <p class="waiting-notice">转移操作中，请勿关闭页面...</p>
    </div>
    <div class="download-cont">
        <div class="download-btn transfer-vip">
            转移会员
        </div>
    </div>
</div>
</body>
<script>
    $('.transfer-vip').click(function() {
        var seeUuid = $('#see-uuid').val();
        console.log(seeUuid);
        if (seeUuid === 'undefined' || seeUuid.length <= 0){
            $('.error-notice').show();
        }else{
            var wnotice = $('.waiting-notice');
            wnotice.show();
            $.getJSON('/api/see/transfer/' + seeUuid + '/{{$uuid}}/{{$token}}', function (returnVal) {
                var snotice = $('.success-notice');
                var enotice = $('.error-notice');
                if (returnVal.code == 200) {
                    wnotice.hide();
                    snotice.text(returnVal.msg);
                    snotice.show();
                    enotice.hide();
                } else {
                    wnotice.hide();
                    enotice.text(returnVal.msg);
                    enotice.show();
                    snotice.hide();
                }
            })
        }
    });
</script>
</html>
