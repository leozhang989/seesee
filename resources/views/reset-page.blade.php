<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/bootstrap.min.css') }}" />
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/bootstrap-grid.min.css') }}" />
        <script type="text/javascript" src="{{ URL::asset('/js/jquery-3.3.1.min.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('/js/bootstrap.min.js') }}"></script>

        <title>重置密码</title>


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
                <form action="/api/reset-pwd" method="post" onSubmit="return closeButton()" name="resetForm">
                    <p class="text-center">{{$email}}</p>
                    <div class="form-group">
                        <input name="new-pwd" type="password" class="form-control" id="password" placeholder="新密码">
                    </div>
                    <div class="form-group">
                        <input name="new-pwd-confirm" type="password" class="form-control" id="password-confirm" placeholder="新密码确认">
                    </div>
                    <input type="hidden" value="{{$resetToken}}" id="reset-token" name="reset-token">
                    <input type="hidden" value="{{$email}}" id="email" name="email">
                    <button name="submitButton" type="submit" class="btn btn-primary btn-block" id="reset-pwd">重置密码</button>
{{--                    <small id="tips" class="form-text text-success hide"></small>--}}
{{--                    <small id="errors" class="form-text text-danger hide"></small>--}}
                </form>
            </div>
        </div>
    </body>
    <script>
        function closeButton(){
            var subBtu = document.getElementById("reset-pwd");
            subBtu.disabled = true;
            subBtu.classList.remove("btn-primary");
            subBtu.classList.add("btn-secondary");
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
