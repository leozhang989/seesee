<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>下载通知</title>
    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #505050;
            font-family: sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            /*display: flex;*/
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .title {
            font-size: 18px;
        }

        .m-b-md {
            margin: 0 12px;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    <div class="content">
        <div class="title m-b-md">
            <p>亲爱的用户：</p>
            <p>苹果对TestFlight版本的VPN审查力度加大，不允许以这种方式继续为用户提供服务，我们正在紧急开发新模式，会更稳定更好用，请大家耐心等待。</p>
            <p>如果你卸载了本地的风速App的用户，联系我们邮箱：fengchi@pm.me，我们会给你新的临时连接方式。</p>
            <p>没有卸载的用户仍可继续使用，只需耐心等待，无需邮件联系我们，我们开发出来会在风速客户端通知到大家，无需担心。</p>
        </div>
    </div>
</div>
</body>
</html>
