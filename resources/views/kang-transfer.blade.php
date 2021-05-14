<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/bootstrap.min.css') }}" />
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/bootstrap-grid.min.css') }}" />
        <script type="text/javascript" src="{{ URL::asset('/js/jquery-3.3.1.min.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('/js/bootstrap.min.js') }}"></script>

        <title>开通kang会员</title>


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
            <div class="container" style="margin: 0 10%;text-align: center;">
                <div class="text-center" style="margin-bottom: 14%;">
                    <img src="{{ asset('seesee/images/see_logo.png') }}" class="rounded" alt="" style="height: 100px;width: auto;">
                </div>
                <form method="post" name="resetForm" id="resetForm">
                    <p class="text-center">仅限开通康转移用户会员</p>
                    <div class="form-group">
                        <input name="account" type="text" class="form-control" id="account" placeholder="see账号或UUID">
                    </div>
                    <div class="form-group">
                        <input name="paytime" type="text" class="form-control" id="paytime" placeholder="康VIP购买时间">
                    </div>
                    <div class="form-group">
                        <input name="pwd" type="text" class="form-control" id="pwd" placeholder="admin密码">
                    </div>
                    <div class="form-group">
                        <input name="code" type="text" class="form-control" id="code" placeholder="康设备码">
                    </div>
                    <small id="tips" class="form-text text-success hide"></small>
                    <small id="errors" class="form-text text-danger hide"></small>
                    <button name="openvip" type="button" class="btn btn-primary btn-block" id="openvip" onclick="sub()">开通see会员</button>
                </form>
            </div>
        </div>
    </body>
    <script>
        const account = $("#account");
        const paytime = $("#paytime");
        const pwd = $("#pwd");
        const code = $("#code");
        account.click(function () {
            $('#errors').hide();
            $('#tips').hide();
        });
        paytime.click(function () {
            $('#errors').hide();
            $('#tips').hide();
        });
        pwd.click(function () {
            $('#errors').hide();
            $('#tips').hide();
        });
        code.click(function () {
            $('#errors').hide();
            $('#tips').hide();
        });

        function sub(){
            if (!account.val()  || !paytime.val() || !pwd.val()){
                $('#errors').text('输入不完整').show();
                return;
            }
            let kangcode = code.val();
            $.ajax({
                type: "GET",
                url: '/api/kang/transfer/' + account.val() + '/' + paytime.val() + '/' + pwd.val() + '/' + kangcode,
                async: false,
                error: function(request) {
                    console.log(request);
                    alert("Connection error:"+request.msg);
                },
                success: function(data) {
                    console.log(data);
                    if (data.code == 200){
                        $('#errors').hide();
                        $('#tips').text(data.msg).show();
                    }else{
                        console.log(data.msg);
                        $('#tips').hide();
                        $('#errors').text(data.msg).show();
                    }
                }
            });
        }
        // $('#reset-pwd').click(function() {
        //     $("#reset-pwd").disabled().removeClass("btn-primary").addClass("btn-secondary");
        //     var url = '/api/reset-pwd';
        //     var token = $("#reset-token").val();
        //     var pwd = $("#password").val();
        //     var new_pwd = $("#password-confirm").val();
        //     var email = $("#email").val();
        //     $.post(url, {'new-pwd' : pwd, 'new-pwd-confirm' : new_pwd, 'reset-token' : token, 'email' : email},function(result){
        //         if (result.code == 200) {
        //             $("#errors").hide();
        //             $("#tips").show().text(result.msg);
        //         }else{
        //             $("#tips").hide();
        //             $("#errors").show().text(result.msg);
        //         }
        //     });
        // });
    </script>
</html>
