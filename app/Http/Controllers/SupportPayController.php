<?php

namespace App\Http\Controllers;

use App\Models\FengRechargeLogs;
use App\Models\FengSettlementList;
use App\Models\FengGoods;
use App\Models\FengUser;
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
//    public function rechargeList(Request $request){
//        if($request->filled('uuid')){
//            $uuid = $request->input('uuid', 0);
//            if(in_array($uuid, ['1011779', '1000047', '1000092'])){
//                $rechargeList = RechargeLogs::where('creater', $uuid)->where('res_status', 1)->orderBy('created_at', 'DESC')->limit(100)->get(['uuid', 'product', 'is_dealed', 'created_at']);
//                return response()->json(['data' => ['list' => $rechargeList], 'msg' => 'success', 'code' => 200]);
//            }
//            return response()->json(['data' => [], 'msg' => '无权限！', 'code' => 202]);
//        }
//        return response()->json(['data' => [], 'msg' => '参数错误！', 'code' => 202]);
//    }

    public function recharge(Request $request){
        if($request->filled('uuid') && $request->filled('user_uuid') && $request->filled('product')) {
            $now = time();
            $uuid = $request->input('uuid', '');
            $userUuid = $request->input('user_uuid', '');
            $todayDate = date('Y-m-d 00:00:00', $now);
            $seeRechargeLogAmount = RechargeLogs::where('creater', $uuid)->where('created_at', '>=', $todayDate)->count();
            $fengRechargeLogAmount = FengRechargeLogs::where('creater', $uuid)->where('created_at', '>=', $todayDate)->count();
            $totalAmount = $seeRechargeLogAmount + $fengRechargeLogAmount;
            $rechargeSettings = SystemSetting::getValueByName('rechargeLimitPerDay') ? : 100;
            if($totalAmount >= $rechargeSettings)
                return response()->json(['msg' => '已达到每日支付数量上限', 'data' => [], 'code' => 202]);

            $length = strlen($userUuid);
            if (in_array($uuid, ['1011779', '1000047', '1000092']) && $userUuid && in_array($request->input('product'), [6, 12]) && in_array($length, [7, 10])) {
                if ($length == 7) {
                    $device = Device::where('uuid', $userUuid)->where('status', 1)->first();
                    $user = $device ? Appuser::find($device['uid']) : '';
                    $good = Goods::where('status', 1)->where('service_date', $request->input('product'))->first();
                }

                if($length == 10){
                    $user = FengUser::where('uuid', $userUuid)->first() ? : '';
                    $good = FengGoods::where('status', 1)->where('service_date', $request->input('product'))->first();
                }

                if(empty($user))
                    return response()->json(['msg' => '用户不存在，请检查输入的uuid是否正确', 'data' => [], 'code' => 202]);
                if(empty($good))
                    return response()->json(['msg' => '产品不存在', 'data' => [], 'code' => 202]);

                DB::beginTransaction();
                try {
                    if($length == 7) {
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
                    }
                    if($length == 10) {
                        FengRechargeLogs::create([
                            'creater' => $uuid,
                            'uuid' => $userUuid,
                            'product' => $request->input('product'),
                            'price' => $good['price'],
                            'is_dealed' => 0,
                            'res_status' => 1,
                            'app_name' => 'Feng',
                            'settlement_id' => 0
                        ]);
                        $vipExpireAt = $user->vip_expireat > $now ? $user->vip_expireat : $now;
                        $user->vip_expireat = strtotime('+' . $request->input('product') . ' month', $vipExpireAt);
                    }
                    if (!$user->save())
                        throw new \Exception('开通失败');

                    DB::commit();
                    $productName = $request->input('product') == 6 ? '半年包' : '一年包';
                    //see data
                    $seeHalf = RechargeLogs::where('is_dealed', 0)->where('app_name', 'See')->where('product', '6')->where('creater', $uuid)->where('res_status', 1)->count();
                    $seeOne = RechargeLogs::where('is_dealed', 0)->where('app_name', 'See')->where('product', '12')->where('creater', $uuid)->where('res_status', 1)->count();
                    $seeTotalMoney = RechargeLogs::where('creater', $uuid)->where('app_name', 'See')->where('res_status', 1)->where('is_dealed', 0)->sum('price');
                    $seeRechargeList = RechargeLogs::where('creater', $uuid)->where('res_status', 1)->orderBy('created_at', 'DESC')->limit(100)->get(['uuid', 'product', 'is_dealed', 'created_at'])->toArray();

                    //feng data
                    $fengHalf = FengRechargeLogs::where('is_dealed', 0)->where('app_name', 'Feng')->where('product', '6')->where('creater', $uuid)->where('res_status', 1)->count();
                    $fengOne = FengRechargeLogs::where('is_dealed', 0)->where('app_name', 'Feng')->where('product', '12')->where('creater', $uuid)->where('res_status', 1)->count();
                    $fengTotalMoney = FengRechargeLogs::where('creater', $uuid)->where('app_name', 'Feng')->where('res_status', 1)->where('is_dealed', 0)->sum('price');
                    $fengRechargeList = FengRechargeLogs::where('creater', $uuid)->where('res_status', 1)->orderBy('created_at', 'DESC')->limit(100)->get(['uuid', 'product', 'is_dealed', 'created_at'])->toArray();

                    $successMsg = '用户 ID：' . $userUuid . '，已开通' . $productName;
                    $data['rechargeList'] = array_merge($seeRechargeList, $fengRechargeList);
                    $data['seeGoods_6'] = $seeHalf;
                    $data['seeGoods_12'] = $seeOne;
                    $data['fengGoods_6'] = $fengHalf;
                    $data['fengGoods_12'] = $fengOne;
                    $data['totalMoney'] = $seeTotalMoney + $fengTotalMoney;
                    $data['payMoney'] = $data['totalMoney'] - ($seeHalf + $seeOne + $fengHalf + $fengOne) * 15;
                    $data['earnMoney'] = $data['totalMoney'] - $data['payMoney'] > 0 ? $data['totalMoney'] - $data['payMoney'] : 0;
                    $data['totalMoney'] = $seeTotalMoney + $fengTotalMoney;
                    return response()->json(['msg' => $successMsg, 'data' => $data, 'code' => 200]);
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
                $seeRes = RechargeLogs::where('creater', $uuid)->where('app_name', 'See')->where('res_status', 1)->where('is_dealed', 0)->get();
                $fengRes = FengRechargeLogs::where('creater', $uuid)->where('app_name', 'Feng')->where('res_status', 1)->where('is_dealed', 0)->get();
                if($seeRes){
                    $seeTotalMoney = $seeRes->sum('price');
                    $seeAmount = count($seeRes);
                    $settleRes = SettlementList::create([
                        'settlement_user' => $uuid,
                        'settlement_status' => 0,
                        'settlement_amount' => $seeAmount,
                        'total_money' => $seeTotalMoney,
                        'earn_money' => 15 * $seeAmount,
                        'pay_money' => $seeTotalMoney - 15 * $seeAmount > 0 ? $seeTotalMoney - 15 * $seeAmount : 0
                    ]);
                    foreach ($seeRes as $recharge) {
                        $recharge->settlement_id = $settleRes['id'];
                        $recharge->is_dealed = 1;
                        $recharge->save();
                    }
                    $settleRes->settlement_status = 1;
                    $settleRes->save();
                }
                if($fengRes){
                    $fengTotalMoney = $fengRes->sum('price');
                    $fengAmount = count($fengRes);
                    $settleRes = FengSettlementList::create([
                        'settlement_user' => $uuid,
                        'settlement_status' => 0,
                        'settlement_amount' => $fengAmount,
                        'total_money' => $fengTotalMoney,
                        'earn_money' => 15 * $fengAmount,
                        'pay_money' => $fengTotalMoney - 15 * $fengAmount > 0 ? $fengTotalMoney - 15 * $fengAmount : 0
                    ]);
                    foreach ($fengRes as $recharge) {
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
