<?php

namespace App\Http\Controllers;

use App\Models\Appuser;
use App\Models\Device;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function register(Request $request){
        if($request->filled('email') && $request->filled('password') && $request->filled('device_code')) {
            $isExisted = Appuser::where('email', $request->input('email'))->first(['id']);
            if ($isExisted)
                return response()->json(['data' => [], 'msg' => '该邮箱已注册过，请直接登录', 'code' => 202]);

            $deviceRes = Device::where('device_code', $request->input('device_code'))->first();
            if(empty($deviceRes))
                return response()->json(['data' => [], 'msg' => '设备无效', 'code' => 202]);

            $now = time();
//            $today = strtotime(date('Y-m-d', $now));
            //首次注册时分配广告会员分组
//            $groupInfo =  '';
            //没有分组则创建分组
//            if(empty($groupInfo)){
//                $groupInfo = AdGroup::create([
//                    'name' => '',
//                    'max_device' => SystemSetting::getValueByName('feng_adVIPAmountPerGroup', 3000),
//                    'used' => 0,
//                    'is_full' => 0,
//                ]);
//            }
            $insertData = [
                'name' => '',
                'gid' => 0,
                'email' => $request->input('email'),
                'password' => MD5($request->input('password')),
                'phone' => '',
                'free_vip_expired' => $deviceRes['free_vip_expired'] > $now ? $deviceRes['free_vip_expired'] : 0,
                'vip_expired' => 0,
                'vip_left_time' => 0
            ];
            $userInfo = Appuser::create($insertData);
            if ($userInfo) {
                //update device uid
                $deviceRes->uid = $userInfo['id'];
                $deviceRes->save();
                return response()->json(['data' => [], 'msg' => '注册成功', 'code' => 200]);
            }
        }
        return response()->json(['data' => [], 'msg' => '注册失败，请重试！', 'code' => 202]);
    }
}
