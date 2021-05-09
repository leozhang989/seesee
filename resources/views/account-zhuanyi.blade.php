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
    </style>
</head>
<body style="display: block;margin: 1em;">
<h1 class="title">转移通知</h1>
<div class="article"
     style="display: block;font-weight: 400;line-height: 1.8;margin-bottom: 70px;word-break: break-word;">
    <p>亲爱的用户您好：</p>
    <p>目前使用的See TestFlight地址失效，后续将无法继续更新，所有的用户转移到新APP。</p>
    <p>您已注册过See APP账号，您的账号是 {{$email}}，请在升级APP后直接登录新APP即可继续使用。</p>
    <p><strong>如有问题联系邮箱：xunjie@protonmail.com</strong></p>
</div>
</body>
</html>