<?php

namespace App\Http\Controllers;

use App\Models\RechargeLogs;
use App\Models\SettlementList;
use Illuminate\Http\Request;

class SupportPayController extends Controller
{
    public function rechargeList(Request $request){
        if($request->filled('uuid')){
            $uuid = $request->input('uuid', 0);
            if(in_array($uuid, ['1011779', '1000047', '1000092'])){
                $rechargeList = RechargeLogs::where('creater', $uuid)->where('res_status', 1)->orderBy('created_at', 'DESC')->limit(100)->get(['uuid', 'product', 'created_at']);
                return response()->json(['data' => ['list' => $rechargeList], 'msg' => 'success', 'code' => 200]);
            }
            return response()->json(['data' => [], 'msg' => '无权限！', 'code' => 202]);
        }
        return response()->json(['data' => [], 'msg' => '参数错误！', 'code' => 202]);
    }

    public function recharge(Request $request){

    }

    public function settlement(Request $request){
        if($request->filled('uuid')){
            $uuid = $request->input('uuid', 0);
            if(in_array($uuid, ['1011779', '1000047', '1000092'])){
                $res = RechargeLogs::where('creater', $uuid)->where('res_status', 1)->where('is_dealed', 0)->get();
                if($res){
                    $amount = count($res);
                    $settleRes = SettlementList::create([
                        'settlement_user' => $uuid,
                        'settlement_status' => 0,
                        'settlement_amount' => $amount,
                        'total_money' => 0,
                        'earn_money' => 0,
                        'pay_money' => 0,
                    ]);
                    foreach ($res as $recharge) {
                        $recharge->settlement_id = $settleRes['id'];
                        $recharge->is_dealed = 1;
                        $recharge->save();
                    }
                }
                return response()->json(['data' => [], 'msg' => 'success', 'code' => 200]);
            }
            return response()->json(['data' => [], 'msg' => '无权限！', 'code' => 202]);
        }
        return response()->json(['data' => [], 'msg' => '参数错误！', 'code' => 202]);
    }
}
