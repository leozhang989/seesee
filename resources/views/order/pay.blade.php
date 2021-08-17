<!DOCTYPE html>
<html>
<head>
    <title></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/bootstrap.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/bootstrap-grid.min.css') }}" />
</head>
<body>
<div class="container" style="margin: 30px auto;">
    <div class="row" style="color:red;">
        <h2 class="col-sm-12 text-center">二维码转账</h2>
    </div>
    <div class="row">
        <p class="text-left col-sm-12" style="margin-top: 20px;">
            亲爱的用户：
        </p>
    </div>
    <div class="row">
        <p class="text-left col-sm-12">
            会员价格：<span style="color:red;">1个月30元、半年108元、一年156元</span>
        </p>
    </div>
    <div class="row">
        <p class="text-left col-sm-12" style="margin-top: 20px;">
            1、长按 支付宝付款码 保存到手机相册
        </p>
    </div>
    <div class="row">
        <div class="col-sm-12 text-center" style="margin-bottom: 20px;">
            <img class="img-responsive" src="{{ asset('seesee/images/qrcode.png') }}" style="width:80%;height: auto;">
        </div>
    </div>
    <div class="row">
        <p class="text-left col-sm-12">
            2、支付宝转账并添加备注，，备注填写See ID，你的<span style="color:red;">See ID：{{$uuid}}</span>
        </p>
        <p class="col-sm-12">
            （See ID也可在See App更多页面找到）
        </p>
    </div>
    <div class="row">
        <div class="col-sm-6 text-center" style="margin-top:20px;">
            <img class="" src="{{ asset('seesee/images/step2.png') }}" style="width:80%;height: auto;">
        </div>
        <div class="col-sm-6 text-center" style="margin-top: 20px;">
            <img class="" src="{{ asset('seesee/images/step1.png') }}" style="width:80%;height: auto;">
        </div>
    </div>
    <div class="row" style="margin-top: 20px;">
        <p class="text-left col-sm-12">如有问题联系邮箱：xunjie@protonmail.com</p>
    </div>
</div>
</body>
</html>
