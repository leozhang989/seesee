<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport"
          content="width=device-width, height=device-height, inital-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>DNS配置完整教程</title>
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
<h1 class="title">DNS配置完整教程</h1>
<div class="article" style="display: block;font-weight: 400;line-height: 1.8;margin-bottom: 20px;word-break: break-word;">
    <ol>
        <li>打开设置- 无线局域网 - 已连接的WIFI - 点击最右侧的“i”标识</li>
    </ol>
    <div class="image-package">
        <div class="image-container" style="max-width: 100%; background-color: transparent;">
            <div class="image-view" data-width="500" data-height="460"><img style="cursor: zoom-in;width: 80%;height: auto;" class="" src="http://216.24.190.156/images/2.png"></div>
        </div>
    </div>
    <ol start="2">
        <li>找到DNS，选择配置DNS</li>
    </ol>
    <div class="image-package">
        <div class="image-container" style="max-width: 100%; background-color: transparent;">
            <div class="image-view" data-width="500" data-height="468"><img style="cursor: zoom-in;width: 80%;height: auto;" class="" src="http://216.24.190.156/images/3.png"></div>
        </div>
    </div>
    <ol start="3">
        <li>选择手动， 删除原来的DNS，添加新服务器223.5.5.5</li>
    </ol>
    <div class="image-package">
        <div class="image-container" style="max-width: 100%; background-color: transparent;">
            <div class="image-view" data-width="500" data-height="627"><img style="cursor: zoom-in;width: 80%;height: auto;" class="" src="http://216.24.190.156/images/4.png"></div>
        </div>
    </div>
    <ol start="4">
        <li>点击存储后， 重新打开Safari浏览器，刷新下载链接即可。</li>
    </ol>
    <hr>
    <blockquote>
        <p><strong>关于DNS服务器，一个失效就换另外一个，多试验几次总有个能用的</strong><br>
            <strong>DNS1：</strong>218.85.157.99<br>
            <strong>DNS2：</strong>218.85.152.99<br>
            <strong>DNS3：</strong>114.114.114.114</p>
    </blockquote>
</div>
</body>
</html>
