<!DOCTYPE html>
<html>
<head>
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/bootstrap.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/bootstrap-grid.min.css') }}" />
    <script src="https://cdn.paddle.com/paddle/paddle.js"></script>
    <script type="text/javascript" src="{{ URL::asset('/js/jquery-3.3.1.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('/js/bootstrap.min.js') }}"></script>
    <style type="text/css">
        body{
            background: #fff;
        }
        #content{
            height: 40px;
        }
        .list-group-item{
            border:0;
            background-color:#fff;
        }
        .item .name{
            height: 50px;
            line-height: 50px;
            padding-left: 40px;
            font-size: 17px;
            font-weight: 500;
            color: #fff;
        }
        .item .price{
            height: 50px;
            line-height: 50px;
            padding-right: 40px;
            font-size: 17px;
            font-weight: 500;
            color: #fff;
            text-align: right;
        }

    </style>

    <script type="text/javascript">
        Paddle.Setup({
            vendor: {{$vendorId}}
        });
    </script>
</head>
<body>

<div class="container">
    <div class="col-sm-12">
        <p class="text-left" style="color: red;margin-top: 20px;">
            目前支持Visa、Master信用卡、Paypal支付。
        </p>
    </div>
    <div id="content" tabindex="-1">
    </div>
    <div class="container">
        <div class="row text-center">
            <div class="col-sm-12" style="color:#F1502A;border:1px dashed #F1502A;border-radius: 24px 24px;display:table;height:100px;">
                <p style="display:table-cell;vertical-align:middle;">
                    <b>购买成功后，重启See APP</b><br/>
                    <b>有效期如未改变，请联系邮箱：</b><br/>
                    <b>fengchi@protonmail.com</b>
                </p>
            </div>
            <div class="list-group col-sm-12" style="margin-top: 10px;">
                @foreach ($goodsList as $key => $goods)
                <a href="javascript:;" data-product="{{$goods['commodity_code']}}" class="list-group-item"  style="margin-top: 30px;">
                    <div class="row item item{{$key%2}}" style="margin: 0 10px;">
                        <div class="col-sm-6 col-6 text-left name">{{$goods['name']}}</div>
                        <div class="col-sm-6 col-6 text-right price">￥{{$goods['price']}}</div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="text-align: center;">
                <h4 class="modal-title" id="myModalLabel">支付通知</h4>
            </div>
            <div class="modal-body" style="text-align: center;color: #5B5B5B;">
                <p>购买成功！</p>
                <p>有效期至：<span id="viptime"></span></p>
                <p>如果是电脑浏览器支付成功的用户，重启See APP即可。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('a[data-product]').click(function() {
        var commodityCode = $(this).attr('data-product');
        $.getJSON('/api/order/create?token={{$token}}&commodityCode='+commodityCode,function(returnVal){
            console.log(returnVal);
            if(returnVal.code == 200){
                var NO = returnVal.data;
                Paddle.Checkout.open({
                    product: commodityCode,
                    allowQuantity:false,
                    passthrough: NO,
                    successCallback: function(data) {
                        console.log(data);
                        // var checkoutId = data.checkout.id;
                        if (data.checkout.completed == true) {
                            $.getJSON('/api/user/vip-time?order_no=' + data.checkout.passthrough, function (vipdata) {
                                if (vipdata.code == 200) {
                                    var vipExpireAt = vipdata.data.vip_expireat;
                                    $('#viptime').text(vipExpireAt);
                                    var pcFlag = IsPC();
                                    console.log(pcFlag);
                                    if (pcFlag) {
                                        $('#myModal').modal('show');
                                    }
                                    try {
                                        window.webkit.messageHandlers.reloadPurchaseInfo.postMessage(vipExpireAt);
                                    } catch (error) {
                                        console.log('reloadPurchaseInfo');
                                    }
                                }
                            });
                        }



                        // Paddle.Order.details(checkoutId, function(data) {
                        //     console.log(data.order);
                        //     if (data.state == 'processed') {
                        //         $.getJSON('/api/user/vip-time?order_no=' + data.order.order_id, function (vipdata) {
                        //             if (vipdata.code == 200) {
                        //                 var vipExpireAt = vipdata.data.vip_expireat;
                        //                 $('#viptime').text(vipExpireAt);
                        //                 $('#myModal').modal('show');
                        //                 try {
                        //                     window.webkit.messageHandlers.reloadPurchaseInfo.postMessage(vipExpireAt);
                        //                 } catch (error) {
                        //                     console.log('reloadPurchaseInfo');
                        //                 }
                        //             }
                        //         });
                        //     }
                        // });

                        {{--$.getJSON('/api/user/vip-time?token={{$token}}',function(vipdata){--}}
                        //     if(vipdata.code == 200){
                        //         var vipExpireAt = vipdata.data.vip_expireat;
                        //         $('#viptime').text(vipExpireAt);
                        //         $('#myModal').modal('show');
                        //         try {
                        //             window.webkit.messageHandlers.reloadPurchaseInfo.postMessage(vipExpireAt);
                        //         } catch(error) {
                        //             console.log('reloadPurchaseInfo');
                        //         }
                        //     }
                        // });
                    },
                    closeCallback: function(data) {
                        console.log(data);
                        alert('支付取消！');
                    }
                });
            }
        });
    });

    // $('a.test-button').click(function() {
    //     $.getJSON('/api/user/vip-time?order_no=3502', function (vipdata) {
    //         if (vipdata.code == 200) {
    //             var vipExpireAt = vipdata.data.vip_expireat;
    //             $('#viptime').text(vipExpireAt);
    //             var pcFlag = IsPC();
    //             console.log(pcFlag);
    //             if (pcFlag) {
    //                 $('#myModal').modal('show');
    //             }
    //             try {
    //                 window.webkit.messageHandlers.reloadPurchaseInfo.postMessage(vipExpireAt);
    //             } catch (error) {
    //                 console.log('reloadPurchaseInfo:' + error);
    //             }
    //         }
    //     });
    // });

    function IsPC() {
        var userAgentInfo = navigator.userAgent;
        var Agents = ["Android", "iPhone",
            "SymbianOS", "Windows Phone",
            "iPad", "iPod"];
        var flag = true;
        for (var v = 0; v < Agents.length; v++) {
            if (userAgentInfo.indexOf(Agents[v]) > 0) {
                flag = false;
                break;
            }
        }
        return flag;
    }

</script>
</body>
</html>
