<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/bootstrap.min.css') }}" />
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/bootstrap-grid.min.css') }}" />
        <script type="text/javascript" src="{{ URL::asset('/js/jquery-3.3.1.min.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('/js/bootstrap.min.js') }}"></script>

        <title>手动转移vip</title>


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
                <form action="#" method="post" onSubmit="return false" name="setForm">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-10">
                                <input name="uuid" type="text" class="form-control" id="uuid" placeholder="小花uuid">
                            </div>
                            <div class="col-md-2">
                                <button name="queryuuid" class="btn btn-primary btn-block" id="queryuuid">查询VIP</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <input name="email" type="email" class="form-control" id="email" placeholder="see邮箱账号">
                    </div>
                    <div class="form-group">
                        <input name="viptime" type="number" class="form-control" id="viptime" placeholder="开通天数">
                    </div>
                    <div class="form-group">
                        <input name="adminpass" type="number" class="form-control" id="adminpass" placeholder="管理员操作密码">
                    </div>
                    <input type="hidden" value="{{$token}}" name="token" id="token">
                    <button name="submitButton" type="submit" class="btn btn-primary btn-block" id="set-vip">开通VIP</button>
                    <small id="tips" class="form-text text-success hide"></small>
                    <small id="errors" class="form-text text-danger hide"></small>
                </form>
            </div>
        </div>
    </body>
    <script>
        // function closeButton(){
        //     var subBtu = document.getElementById("reset-pwd");
        //     subBtu.disabled = true;
        //     subBtu.classList.remove("btn-primary");
        //     subBtu.classList.add("btn-secondary");
        // }
        $('#set-vip').click(function() {
            // $("#set-vip").disabled().removeClass("btn-primary").addClass("btn-secondary");
            var url = '/api/set-vip';
            var uuid = $("#uuid").val();
            var email = $("#email").val();
            var viptime = $("#viptime").val();
            var adminpass = $("#adminpass").val();
            var token = $("#token").val();
            $.post(url, {'uuid' : uuid, 'email' : email, 'viptime' : viptime, 'adminpass': adminpass, 'token': token},function(result){
                if (result.code == 200) {
                    $("#errors").hide();
                    $("#tips").show().text(result.msg);
                }else{
                    $("#tips").hide();
                    $("#errors").show().text(result.msg);
                }
            });
        });

        $('#queryuuid').click(function() {
            var url = '/api/query-flower-vip';
            var uuid = $("#uuid").val();
            var token = $("#token").val();
            $.post(url, {'uuid' : uuid, 'token': token},function(result){
                if (result.code == 200) {
                    $("#queryerrors").hide();
                    $("#querytips").show().text(result.msg);
                }else{
                    $("#querytips").hide();
                    $("#queryerrors").show().text(result.msg);
                }
            });
        });
    </script>
</html>
