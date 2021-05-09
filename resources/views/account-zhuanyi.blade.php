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
        .email-collect input {
            padding: 8px;
            height: 24px;
            line-height: 24px;
            width: 100%;
            border: solid 2px #dc5b41;
            border-radius: 6px;
        }

        .download-btn a {
            display: inline-block;
            width: 100%;
            height: 50px;
            line-height: 50px;
            text-decoration: none;
            color: #fff;
        }

        .email-input input {
            padding: 8px;
            height: 24px;
            line-height: 24px;
            width: 100%;
            border: solid 2px #dc5b41;
            border-radius: 6px;
        }

        .download-cont{
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
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
    </style>
</head>
<body style="display: block;margin: 1em;">
<h1 class="title">转移通知</h1>
<div class="article"
     style="display: block;font-weight: 400;line-height: 1.8;margin-bottom: 70px;word-break: break-word;">
    <p>亲爱的用户您好：</p>
    <p>目前使用的See TestFlight地址失效，所有的用户转移到新APP，下面是最新的TestFlight下载地址。</p>
    <p>您已注册过See APP账号，您的账号是 <strong>{{$email}}</strong>，请在升级APP后直接登录新APP即可继续使用。</p>
    <div class="download-cont">
        <div class="download-btn">
            <a class="download-link" href="https://testflight.apple.com/join/KoHLIzlw">下载 新版本See</a>
        </div>
    </div>
    <p>忘记密码的直接找回密码就可以了，注意查看邮件垃圾箱。</p>
    <p><strong>如有问题联系邮箱：xunjie@protonmail.com</strong></p>
</div>
</body>
</html>
