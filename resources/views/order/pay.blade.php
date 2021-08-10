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
            价格：1个月30元、半年98元、一年138元
        </p>
    </div>
    <div class="row">
        <img class="img-responsive text-center col-sm-12" src="{{ asset('seesee/images/qrcode.png') }}" style="width:100%;height: auto;">
    </div>
    <div class="row">
        <p class="text-left col-sm-12" style="margin-top: 20px;">
            1、长按 支付宝付款码 保存到手机相册
        </p>
    </div>
    <div class="row">
        <p class="text-left col-sm-12">
            2、支付宝转账并添加备注，备注一定要写上风速ID（风速ID在风速设置页面，点击复制即可）
        </p>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <img class="text-center" src="{{ asset('seesee/images/step1.png') }}" style="width:100%;height: auto;">
        </div>
        <div class="col-sm-6">
            <img class="text-center" src="{{ asset('seesee/images/step2.png') }}" style="width:100%;height: auto;">
        </div>
    </div>
    <div class="row" style="margin-top: 20px;">
        <p class="text-left col-sm-12">如有问题联系邮箱：fengchi@pm.me</p>
    </div>
</div>
</body>
</html>
