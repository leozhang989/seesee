<?php

namespace App\Http\Controllers;

use App\Models\Appuser;
use App\Models\Device;
use App\Models\Goods;
use App\Models\Order;
use App\Models\PaddlePayment;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayController extends Controller
{
    public function list(Request $request, $token = '')
    {
        if ($token) {
            $device = Device::where('uuid', $token)->first();
            if(empty($device))
                die('支付异常');

            if($device['uid'] == 0)
                die('用户账户异常，请先注册账号');

            $goodsList = Goods::where('status', 1)->get();
            $viewData = [
                'goodsList' => $goodsList,
                'title' => '商品列表',
                'token' => $token,
                'vendorId' => SystemSetting::getValueByName('vendorId')
            ];
            return view('order.payment', $viewData);
        }
        die('支付链接异常');
    }

    public function createOrder(Request $request)
    {
        if ($request->filled('token') && $request->filled('commodityCode')) {
            $goods = Goods::where('commodity_code', $request->input('commodityCode'))->first();
            if (!$goods)
                return response()->json(['msg' => '商品不存在', 'data' => [], 'code' => 202]);

            $deviceInfo = Device::where('uuid', $request->input('token'))->first();
            if(empty($deviceInfo) || empty($deviceInfo['uid']))
                return response()->json(['msg' => '设备不存在或您还未注册', 'data' => [], 'code' => 202]);

            $UserInfo = Appuser::find($deviceInfo['uid']);
            if (empty($UserInfo))
                return response()->json(['msg' => '用户信息不存在，请先注册成为会员', 'data' => [], 'code' => 202]);

//            if ($order = Order::where('commodity_code', $request->input('commodityCode'))->where('uuid', $request->input('token'))->where('status', 0)->first())
//                return response()->json(['msg' => '成功', 'data' => $order['id'], 'code' => 200], 200);

            $insertData = [
                'uuid' => $request->input('token'),  //用户id
                'goods_id' => $goods['id'],
                'goods_name' => $goods['name'],
                'goods_num' => 1,
                'service_date' => $goods['service_date'],
                'commodity_code' => $goods['commodity_code'],
                'price' => $goods['price'],
                'status' => 0
            ];
            $newOrder = Order::create($insertData);
            if ($newOrder)
                return response()->json(['msg' => '成功', 'data' => $newOrder['id'], 'code' => 200]);
        }
        return response()->json(['msg' => '参数错误', 'data' => [], 'code' => 202]);
    }

    public function getVipexpireat(Request $request)
    {
        $orderNo = $request->input('order_no', '');
        if (empty($orderNo))
            return response()->json(['msg' => '参数错误', 'data' => [], 'code' => 202]);

        $order = Order::find($orderNo);
        if($order) {
            $deviceInfo = Device::where('uuid', $order['uuid'])->first();
            $user = Appuser::where('id', $deviceInfo['uid'])->first();
            $vipExpireAt = ($user && $user['vip_expired']) ? date('Y-m-d H:i:s', $user['vip_expired']) : '';
            return response()->json(['msg' => '成功', 'data' => ['vip_expireat' => $vipExpireAt], 'code' => 200]);
        }
        return response()->json(['msg' => '查无此订单', 'data' => [], 'code' => 202]);
    }


    public function webHook(Request $request)
    {
//        Log::channel('paylog')->info('web-hook callback data:' . json_encode($request->all()));
        $signature = $request->input('p_signature');
        if (!$this->verifyHook($signature, $request->all())) {
            return response()->json('Security check failure!', 202);
        }

        $alert_name = $request->input('alert_name');
        if(empty($alert_name))
            return response()->json('no alert_name', 202);

        $passthrough = $request->input('passthrough');

        if ($alert_name === 'payment_succeeded' && $passthrough) {
            $currency = $request->input('currency');
            $email = $request->input('email');
            $customer_name = $request->input('customer_name');
            $payment_method = $request->input('payment_method');
            $earnings = $request->input('earnings');
            $fee = $request->input('fee');
            $sale_gross = $request->input('sale_gross');
            $quantity = $request->input('quantity');
            $product_id = $request->input('product_id');
            $product_name = $request->input('product_name');
            $order_id = $request->input('order_id');

            $order = Order::find($passthrough);
            if (empty($order)) {
                return response()->json('no record in local', 202);
            }
            if ($order['status'] === 1) {  //已处理订单
                return response()->json('already handle this record');
            }
            $paymentData = [
                'currency' => $currency ? : '',
                'checkout_id' => '',
                'email' => $email ? : '',
                'customer_name' => $customer_name ? : '',
                'payment_method' => $payment_method ? : '',
                'earnings' => $earnings ? : '',
                'fee' => $fee ? : 0,
                'sale_gross' => $sale_gross ? : 0,
                'quantity' => $quantity ? : 0,
                'product_id' => $product_id ? : 0,
                'product_name' => $product_name ? : '',
                'order_id' => $order_id ? : '',
                'order_no' => $passthrough
            ];

            //验签成功,向数据库添加订单数据，方便记录用户支付的记录
            $paddlePayment = PaddlePayment::where('order_no', $paymentData['order_no'])->first();
            if (!$paddlePayment) {
                $paddlePayment = PaddlePayment::create($paymentData);
            }
            if ($paddlePayment) {
                $order = Order::where('id', $passthrough)->first();
                $order->status = 1;
                $order->order_no = $paymentData['order_id'];
                $order->transactionId = $paymentData['order_id'];
                if ($order->save()) {
                    $month = $order->service_date * $order->goods_num;
                    $deviceInfo = Device::where('uuid', $order->uuid)->first();
                    $user = Appuser::find($deviceInfo['uid']);
                    if($user) {
                        $now = time();
                        $vipExpireAt = $user->vip_expired > $now ? $user->vip_expired : $now;
                        $freeLeft = 0;
                        if($user->free_vip_expired > $now && $vipExpireAt == $now)
                            $freeLeft = $user->free_vip_expired - $now;

                        $user->vip_expired = strtotime('+' . $month . ' month', $vipExpireAt) + $freeLeft;
                        if ($user->save())
                            return response()->json('ok');
                    }
                    return response()->json('error', 202);
                }
            }
        }
        return response()->json('error', 202);
    }

    public function verifyHook($signature, $allParams)
    {
        $paddleKey = paddleKey();

        // Get the p_signature parameter & base64 decode it.
        $signature = base64_decode($signature);

        // Get the fields sent in the request, and remove the p_signature parameter
        $fields = $allParams;
        unset($fields['p_signature']);

        // ksort() and serialize the fields
        ksort($fields);
        foreach ($fields as $k => $v) {
            if (!in_array(gettype($v), array('object', 'array'))) {
                $fields[$k] = "$v";
            }
        }
        $data = serialize($fields);

        // Veirfy the signature
        $verification = openssl_verify($data, $signature, $paddleKey, OPENSSL_ALGO_SHA1);
        return (bool)$verification;
    }
}
