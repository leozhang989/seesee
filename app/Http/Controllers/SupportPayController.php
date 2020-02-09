<?php

namespace App\Http\Controllers;

use App\Models\Appuser;
use App\Models\Device;
use App\Models\Goods;
use App\Models\RechargeLogs;
use App\Models\SettlementList;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportPayController extends Controller
{
    public function rechargeList(Request $request){
        if($request->filled('uuid')){
            $uuid = $request->input('uuid', 0);
            if(in_array($uuid, ['1011779', '1000047', '1000092'])){
                $rechargeList = RechargeLogs::where('creater', $uuid)->where('res_status', 1)->orderBy('created_at', 'DESC')->limit(100)->get(['uuid', 'product', 'is_dealed', 'created_at']);
                return response()->json(['data' => ['list' => $rechargeList], 'msg' => 'success', 'code' => 200]);
            }
            return response()->json(['data' => [], 'msg' => '无权限！', 'code' => 202]);
        }
        return response()->json(['data' => [], 'msg' => '参数错误！', 'code' => 202]);
    }

    public function recharge(Request $request){
        if($request->filled('uuid') && $request->filled('user_uuid') && $request->filled('product')) {
            $now = time();
            $uuid = $request->input('uuid', '');
            $userUuid = $request->input('user_uuid', '');
            $todayDate = date('Y-m-d 00:00:00', $now);
            $rechargeLogAmount = RechargeLogs::where('creater', $uuid)->where('created_at', '>=', $todayDate)->count();
            $rechargeSettings = SystemSetting::getValueByName('rechargeLimitPerDay') ? : 100;
            if($rechargeLogAmount >= $rechargeSettings)
                return response()->json(['msg' => '已达到每日支付数量上限', 'data' => [], 'code' => 202]);

            $length = strlen($userUuid);
            $device = Device::where('uuid', $userUuid)->where('status', 1)->first();
            $user = $device ? Appuser::find($device['uid']) : '';
            if (in_array($uuid, ['1011779', '1000047', '1000092']) && $userUuid && $length == 7 && $user && in_array($request->input('product'), [6, 12])) {
                $good = Goods::where('status', 1)->where('service_date', $request->input('product'))->first();
                if(empty($good))
                    return response()->json(['msg' => '产品不存在', 'data' => [], 'code' => 202]);

                DB::beginTransaction();
                try {
                    RechargeLogs::create([
                        'creater' => $uuid,
                        'uuid' => $userUuid,
                        'product' => $request->input('product'),
                        'price' => $good['price'],
                        'is_dealed' => 0,
                        'res_status' => 1,
                        'app_name' => 'See',
                        'settlement_id' => 0
                    ]);
                    $vipExpireAt = $user->vip_expired > $now ? $user->vip_expired : $now;
                    $user->vip_expired = strtotime('+' . $request->input('product') . ' month', $vipExpireAt);
                    if (!$user->save())
                        throw new \Exception('开通失败');
                    DB::commit();
                    $productName = $request->input('product') == 6 ? '半年包' : '一年包';
//                    $seeHalf = RechargeLogs::where('is_dealed', 0)->where('app_name', 'See')->where('product', '6')->where('uuid', $uuid)->where('res_status', 1)->count();
//                    $seeOne = RechargeLogs::where('is_dealed', 0)->where('app_name', 'See')->where('product', '12')->where('uuid', $uuid)->where('res_status', 1)->count();
//                    $totalMoney = RechargeLogs::where('creater', $uuid)->where('app_name', 'See')->where('res_status', 1)->where('is_dealed', 0)->sum('price');
//                    $successMsg = 'See ID：' . $userUuid . '，已开通' . $productName . '。See半年包' . $seeHalf . '个，一年包' . $seeOne . '个。See合计：' . $totalMoney;
                    $successMsg = 'See ID：' . $userUuid . '，已开通' . $productName;
                    return response()->json(['msg' => $successMsg, 'data' => '', 'code' => 200]);
                } catch (\Exception $e) {
                    DB::rollback();
                    $msg = $e->getMessage();
                    return response()->json(['msg' => $msg ? : '开通失败，请重试', 'data' => [], 'code' => 202]);
                }
            }
            return response()->json(['data' => [], 'msg' => '无权限！', 'code' => 202]);
        }
        return response()->json(['data' => [], 'msg' => '参数错误！', 'code' => 202]);
    }

    public function settlement(Request $request){
        if($request->filled('uuid')){
            $uuid = $request->input('uuid', 0);
            if(in_array($uuid, ['1011779', '1000047', '1000092'])){
                $res = RechargeLogs::where('creater', $uuid)->where('res_status', 1)->where('is_dealed', 0)->get();
                if($res){
                    $totalMoney = $res->sum('price');
                    $amount = count($res);
                    $settleRes = SettlementList::create([
                        'settlement_user' => $uuid,
                        'settlement_status' => 0,
                        'settlement_amount' => $amount,
                        'total_money' => $totalMoney,
                        'earn_money' => 15 * $amount,
                        'pay_money' => $totalMoney - 15 * $amount > 0 ? $totalMoney - 15 * $amount : 0
                    ]);
                    foreach ($res as $recharge) {
                        $recharge->settlement_id = $settleRes['id'];
                        $recharge->is_dealed = 1;
                        $recharge->save();
                    }
                    $settleRes->settlement_status = 1;
                    $settleRes->save();
                }
                return response()->json(['data' => [], 'msg' => 'success', 'code' => 200]);
            }
            return response()->json(['data' => [], 'msg' => '无权限！', 'code' => 202]);
        }
        return response()->json(['data' => [], 'msg' => '参数错误！', 'code' => 202]);
    }
}
