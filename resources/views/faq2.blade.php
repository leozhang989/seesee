<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport"
          content="width=device-width, height=device-height, inital-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>部分用户TestFlight无法接入APPStore解决方法</title>
    <style>
        body{
            font-family: -apple-system,SF UI Display,Arial,PingFang SC,Hiragino Sans GB,Microsoft YaHei,WenQuanYi Micro Hei,sans-serif;
            word-break: break-word!important;
            word-break: break-all;
        }
        ol{
            font-size: 16px;
            font-weight: 400;
            line-height: 1.7;
            color: #2f2f2f;
            display: block;
            list-style-type: decimal;
            margin-block-start: 1em;
            margin-block-end: 1em;
            margin-inline-start: 0px;
            margin-inline-end: 0px;
            padding-inline-start: 40px;
        }
        ul{
            padding: 0;
            margin: -5px 0 20px 20px;
            display: block;
            list-style-type: disc;
        }
        ul li{
            display: list-item;
            text-align: -webkit-match-parent;
        }
        code{
            font-family: monospace;
            padding: 2px 4px;
            margin: 2px;
            font-size: 14px;
            white-space: pre-wrap;
            position: relative;
            vertical-align: middle;
            border-radius: 4px;
            color: #c7254e;
            background-color: #f6f6f6;
        }
        p{
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
        .image-view{
            width: 100%;
            height: 100%;
            overflow: hidden;
            text-align: center;
        }
        .image-container{
            position: relative;
            z-index: 95;
            background-color: #e6e6e6;
            transition: background-color .1s linear;
            margin: 0 auto;
        }
        hr{
            margin: 0 0 20px;
            border: 0;
            border-top: 1px solid #eee!important;
            -webkit-box-sizing: content-box;
            box-sizing: content-box;
            height: 0;
            overflow: visible;
        }
        blockquote{
            padding: 20px;
            background-color: #fafafa;
            border-left: 6px solid #e6e6e6;
            word-break: break-word;
            font-size: 16px;
            font-weight: normal;
            line-height: 30px;
            margin: 0 0 20px;
        }
        blockquote p{
            font-weight: 400;
            line-height: 1.7;
        }
        .title{
            margin: 15px 0;
            font-size: 30px;
            font-weight: 700;
            line-height: 1.4;
            color: #2f2f2f;
        }
        h1{
            display: block;
            font-size: 2em;
            margin-block-start: 0.67em;
            margin-block-end: 0.67em;
            margin-inline-start: 0px;
            margin-inline-end: 0px;
            font-weight: bold;
        }
    </style>
</head>
<body style="display: block;margin: 1em;">
<h1 class="title">部分用户Testflight无法连接AppStore Connect的解决方案</h1>
<div class="article" style="display: block;font-weight: 400;line-height: 1.8;margin-bottom: 20px;word-break: break-word;">
    <p>最近部分用户安装TestFlight时一直出现无法接入appstore connect问题，有些客户打开会提示不可用，其实是因为苹果官方对TF签名分发的地域进行了限制，有些地区用户是正常的，有些地区的用户就会收到上面的提示。是苹果服务器的问题，所以大家无须担心，后续会恢复正常。我们提供三种解决方案：</p>
    <div class="image-package">
        <div class="image-container" style="max-width: 100%; background-color: transparent;">
            <div class="image-view" data-width="300" data-height="650"><img style="cursor: zoom-in;width: 80%;height: auto;" class="" src="http://216.24.190.156/images/26.png"></div>
        </div>
    </div>
    <h4 style="font-size: 22px;margin: 20px 0 15px;">方案一：手机上有我们VPN客户端用户</h4>
    <p>进入服务器列表，选择全局服务器中任意一台，连接上以后，重新加载TestFlight。</p>
    <div class="image-package">
        <div class="image-container" style="max-width: 100%; background-color: transparent;">
            <div class="image-view" data-width="500" data-height="460"><img style="cursor: zoom-in;width: 80%;height: auto;" class="" src="http://216.24.190.156/images/25.png"></div>
        </div>
    </div>
    <h4 style="font-size: 22px;margin: 20px 0 15px;">方案二：腾讯加速器 解决方案</h4>
    <p><strong>- 第一步：在苹果 <code>app store</code> -> 搜索 <code>腾讯加速器</code> 并下载，如下：</strong></p>
    <div class="image-package">
        <div class="image-container" style="max-width: 100%; background-color: transparent;">
            <div class="image-view" data-width="500" data-height="460"><img style="cursor: zoom-in;width: 80%;height: auto;" class="" src="http://216.24.190.156/images/21.png"></div>
        </div>
    </div>
    <p><strong>- 第二步：打开<code>腾讯加速器</code>，搜索 <code>steam</code></strong></p>
    <div class="image-package">
        <div class="image-container" style="max-width: 100%; background-color: transparent;">
            <div class="image-view" data-width="500" data-height="627"><img style="cursor: zoom-in;width: 80%;height: auto;" class="" src="http://216.24.190.156/images/23.png"></div>
        </div>
    </div>
    <p><strong>- 第三步：选中 <code>Steam移动版</code> 点击 加速</strong></p>
    <div class="image-package">
        <div class="image-container" style="max-width: 100%; background-color: transparent;">
            <div class="image-view" data-width="500" data-height="627"><img style="cursor: zoom-in;width: 80%;height: auto;" class="" src="http://216.24.190.156/images/24.png"></div>
        </div>
    </div>
    <p>到此就完成了，打开 TestFlight 就可以正常访问了</p>
    <h4 style="font-size: 22px;margin: 20px 0 15px;">方案三：WI-FI DNS 解决方案</h4>
    <p>在手机 设置 -> 无线局域网 -> 选中当前Wi-Fi右侧的 感叹号 ! -> 配置DNS -> 自动改为手动 如下操作。</p>
    <ul>
        <li><code>在配置 DNS中删除之前的DNS，添加新服务器输入谷歌的 DNS ： 8.8.4.4 或者 8.8.8.8</code></li>
        <li><code>在配置 DNS中删除之前的DNS，添加新服务器输入阿里 的 DNS ： 223.5.5.5</code></li>
        <li><code>在配置 DNS中删除之前的DNS，添加新服务器输入电信 的 DNS ： 114.114.114.114</code></li>
    </ul>
    <p><code>注意: 下载完成后，DNS 把手动还原为自动，不然可能影响网速</code></p>
    <p><a href="http://xunjie.in/app/faq1" target="_blank">完整教程</a></p>
</div>
</body>
</html>
