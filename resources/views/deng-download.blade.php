<!DOCTYPE html>
<html lang="en" style="font-size: 50px;">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta name="viewport"
          content="width=device-width, height=device-height, inital-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TestFlight版本下载</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('download-page/css/common.css') }}">
    <style>
        * {
            margin: 0;
        }

        body {
            background: #F7F7F7;
        }

        .wrapper {
            margin: 0.2rem 0.28rem 0;
        }

        .wrapper .title {
            font-size: 0.28rem;
            color: #666;
        }

        .wrapper .title p {
            margin-bottom: 0.4rem;
            height: 0.38rem;
            line-height: 0.38rem;
        }

        .wrapper .title p::before {
            content: '';
            display: inline-block;
            margin-top: -0.05rem;
            width: 0px;
            height: 0px;
            border-top: 0.14rem solid transparent;
            border-left: 0.14rem solid #5468BE;
            border-bottom: 0.14rem solid transparent;
            margin-right: 0.16rem;
            vertical-align: middle;
        }

        .wrapper .item {
            padding: 0 0.3rem;
            width: 100%;
            min-height: 3.46rem;
            height: 100%;
            background: #fff;
            box-sizing: border-box;
        }

        .wrapper .item + .item {
            margin-top: 0.2rem;
        }

        .wrapper .item-title {
            height: 1.04rem;
            line-height: 1.04rem;
            border-bottom: 1px solid #F3F3F3;
        }

        .wrapper .item-title h2 {
            font-size: 0.32rem;
            color: #000;
            letter-spacing: 1px;
        }

        .wrapper .item-title h2 span {
            padding-left: 0.2rem;
            font-size: 0.28rem;
            font-weight: initial;
            letter-spacing: 0.01rem;
        }

        .con {
            display: flex;
            align-items: flex-start;
            margin-top: 0.4rem;
        }

        .con-new{
            align-items: flex-start;
            margin-top: 0.4rem;
            margin-bottom: 0.4rem;
        }

        .con-new h3 {
            line-height: 30px;
        }

        .con-new p {
            line-height: 19px;
        }

        .con .img {
            flex: 0 0 1.6rem;
            margin-right: 0.24rem;
            height: 80px;
            width: 80px;
        }

        .con .between {
            width: 100%;
        }

        .wrapper .con-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.28rem;
        }

        .wrapper .con-item .des {
            font-size: 0.28rem;
            color: #000;
            letter-spacing: 0.01rem;
        }

        .wrapper .con-item .des p:last-child {
            margin-top: 0.04rem;
            font-size: 0.24rem;
            color: #666;
            letter-spacing: 0.0086rem;
        }

        .wrapper .btn {
            width: 1.64rem;
            height: 0.66rem;
            line-height: 0.66rem;
            background: #53afae;
            border-radius: 0.33rem;
            text-align: center;
            font-size: 0.32rem;
            color: #fff;
            letter-spacing: 0.0114rem;
        }

        .wrapper .notes {
            font-size: 0.24rem;
            color: #53afae;
            letter-spacing: 0.43px;
            line-height: 1.5;
        }

        @media screen and (min-width: 768px) {

        }
        .head-title{
            height: 45px;
            line-height: 45px;
            font-size: 1.5em;
        }
    </style>
</head>

<body>
<div class="g-header other-header clearfix">
    <div class="container">
        <div class="head-title pull-left">
            SEE 下载地址
        </div>
        <div class="logo pull-right">
            <img src="{{ asset('download-page/images/deng_head_logo.jpg') }}" alt="">
        </div>
    </div>
</div>
<div class="wrapper">
    <div class="title">
        <p>TestFlight版本下载</p>
    </div>
    <ul>
        <li class="item">
            <div class="item-title">
                <h2>步骤1<span>获取TestFlight</span></h2>
            </div>
            <div class="con">
                <img class="img" src="{{ asset('download-page/images/icon_testflight.png') }}" alt="">
                <div class="between">
                    <div class="con-item">
                        <div class="des">
                            <p>下载 TestFlight</p>
                            <p>苹果官方测试平台</p>
                        </div>
                        <a class="btn" href="https://itunes.apple.com/cn/app/testflight/id899247664?mt=8">安装</a>
                    </div>
                    <span class="notes">注意：若无安装TestFlight，将无法进行步骤2。已安装用户可忽略此步。</span>
                </div>
            </div>
        </li>
        <li class="item">
            <div class="item-title">
                <h2>步骤2<span>获取SEE VPN</span></h2>
            </div>
            <div class="con">
                <img class="img" src="{{ asset('download-page/images/deng_logo.png') }}" alt="">
                <div class="between">
                    <div class="con-item">
                        <div class="des">
                            <p>SEE VPN</p>
                            <p>官方内测版本</p>
                        </div>
                        <a class="btn J-download" data-type="ios" href="{{ $testFlightUrl }}"
                           id="btnIOSDownload">安装</a>
                    </div>
                    <span class="notes">注意：软件本身有效期90天，有效期内会有新版本推出，更新后有效期会重置。</span>
                </div>
            </div>
        </li>
    </ul>
</div>
</body>
</html>
