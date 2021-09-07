<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/bootstrap.min.css') }}" />
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/bootstrap-grid.min.css') }}" />
        <script type="text/javascript" src="{{ URL::asset('/js/jquery-3.3.1.min.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('/js/bootstrap.min.js') }}"></script>

        <title>开通会员VIP</title>


        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: sans-serif,微软雅黑;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .content {
                text-align: center;
            }

            .hide{
                display: none;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="container" style="margin: 0 1rem;text-align: center;">
                <div class="text-center" style="margin-bottom: 14%;">
                    <img src="{{ asset('seesee/images/see_logo.png') }}" class="rounded" alt="" style="height: 100px;width: auto;">
                </div>
                <form method="post" name="resetForm" id="resetForm">
                    <p class="text-center">仅限开通用户VIP使用</p>
                    <input type="hidden" id="creater" class="creater" name="creater" value="1027653">
                    <div class="form-group">
                        <input name="account" type="number" class="form-control" id="account" placeholder="输入用户UUID" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <input name="vipmonth" type="number" class="form-control" id="vipmonth" placeholder="需要开通的月份" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <input name="pwd" type="text" class="form-control" id="pwd" placeholder="admin密码">
                    </div>
                    <small id="tips" class="form-text text-success hide" style="margin-bottom: 0.5rem"></small>
                    <small id="errors" class="form-text text-danger hide" style="margin-bottom: 0.5rem"></small>
                    <button name="openvip" type="button" class="btn btn-primary btn-block" id="openvip" onclick="sub()">开通会员</button>
                </form>
            </div>
        </div>
    </body>
    <script>
        const account = $("#account");
        const vipmonth = $("#vipmonth");
        const creater = $("#creater");
        const pwd = $("#pwd");
        account.click(function () {
            $('#errors').hide();
            $('#tips').hide();
        });
        vipmonth.click(function () {
            $('#errors').hide();
            $('#tips').hide();
        });
        pwd.click(function () {
            $('#errors').hide();
            $('#tips').hide();
        });

        function sub(){
            if (!account.val()  || !vipmonth.val() || !pwd.val()){
                $('#errors').text('输入不完整').show();
                return;
            }
            if (vipmonth.val() != 1 && vipmonth.val() != 6 && vipmonth.val() != 12){
                $('#errors').text('请输入正确月份数字，1、6、12').show();
                return;
            }
            if(confirm("确定给用户 " + account.val() + " 开通 " + vipmonth.val() + " 个月vip吗？")){
                $.ajax({
                    type: "POST",
                    url: '/api/web/recharge',
                    async: false,
                    data: {"user_uuid": account.val(), "product": vipmonth.val(), "pwd": pwd.val(), "uuid": creater.val(), "source": 'web'},
                    error: function(request) {
                        console.log(request);
                        alert("Connection error:"+request.msg);
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.code == 200){
                            $('#errors').hide();
                            $('#tips').text(data.msg).show();
                            account.val("");
                            vipmonth.val("");
                            pwd.val("");
                        }else{
                            console.log(data.msg);
                            $('#tips').hide();
                            $('#errors').text(data.msg).show();
                        }
                    }
                });
            }
        }
    </script>
</html>
