<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/bootstrap.min.css') }}" />
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/bootstrap-grid.min.css') }}" />
        <script type="text/javascript" src="{{ URL::asset('/js/jquery-3.3.1.min.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('/js/bootstrap.min.js') }}"></script>

        <title>进群领福利活动</title>


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
                    进群领福利活动
                </div>
                <form action="##" method="post" onSubmit="return false;" name="resetForm">
                    <div class="form-group">
                        <input name="uuid" type="number" class="form-control" id="uuid" placeholder="输入用户ID">
                    </div>
                    <button name="submitButton" type="submit" class="btn btn-primary btn-block" id="get-gift">领取福利</button>
                    <small id="tips" class="form-text text-success hide"></small>
                    <small id="errors" class="form-text text-danger hide"></small>
                </form>
            </div>
        </div>
    </body>
    <script>
        let ele = $("#get-gift");
        $("#uuid").bind("input propertychange",function(event){
            ele.removeAttr("disabled").removeClass("btn-secondary").addClass("btn-primary")
        });
        ele.click(function() {
            ele.attr('disabled', "true").removeClass("btn-primary").addClass("btn-secondary");
            var url = '/api/get-group-gift';
            var uuid = $("#uuid").val();
            $.post(url, {'uuid' : uuid},function(result){
                console.log(result);
                if (result.code == 200) {
                    $("#errors").hide();
                    $("#tips").show().text(result.msg);
                }else{
                    $("#tips").hide();
                    $("#errors").show().text(result.msg);
                }
            });
        });
    </script>
</html>
